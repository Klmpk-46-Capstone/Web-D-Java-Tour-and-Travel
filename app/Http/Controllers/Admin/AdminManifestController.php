<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Manifes Penumpang & Verifikasi Check-in (super_admin & admin_ops).
 */
class AdminManifestController extends Controller
{
    /** Status booking yang tampil di manifes. */
    private const MANIFEST_STATUSES = ['pending', 'confirmed', 'completed', 'refund_requested'];

    public function index(Request $request): View
    {
        $trips = Trip::with('package')
            ->where('status', '!=', 'cancelled')
            ->orderBy('departure_date')
            ->get();

        $selectedTrip = $this->resolveTrip($request, $trips);
        $filter = (string) $request->query('filter', 'all');
        $search = trim((string) $request->query('q', ''));

        $all = collect();
        if ($selectedTrip) {
            $all = $this->manifestQuery($selectedTrip)->get();
        }

        $passengers = $all
            ->when($search !== '', function ($c) use ($search) {
                $needle = Str::lower($search);

                return $c->filter(fn (Booking $b) => Str::contains(Str::lower(
                    $b->customer_name . ' ' . $b->booking_code . ' ' . $b->customer_phone . ' ' . $b->customer_email
                ), $needle));
            })
            ->when($filter === 'checked_in', fn ($c) => $c->filter(fn (Booking $b) => $b->is_checked_in))
            ->when($filter === 'not_checked_in', fn ($c) => $c->filter(fn (Booking $b) => ! $b->is_checked_in))
            ->when($filter === 'dp', fn ($c) => $c->where('payment_status', 'partial'))
            ->when($filter === 'unpaid', fn ($c) => $c->filter(
                fn (Booking $b) => ! in_array($b->payment_status, ['paid', 'partial'], true)
            ))
            ->values();

        $summary = [
            'total_bookings' => $all->count(),
            'total_pax' => (int) $all->sum('pax'),
            'checked_in_bookings' => $all->filter(fn (Booking $b) => $b->is_checked_in)->count(),
            'checked_in_pax' => (int) $all->filter(fn (Booking $b) => $b->is_checked_in)->sum('pax'),
            'waiting_pax' => (int) $all->filter(fn (Booking $b) => ! $b->is_checked_in)->sum('pax'),
            'waiting_bookings' => $all->filter(fn (Booking $b) => ! $b->is_checked_in)->count(),
            'dp_count' => $all->where('payment_status', 'partial')->count(),
            'unpaid_count' => $all->filter(
                fn (Booking $b) => ! in_array($b->payment_status, ['paid', 'partial'], true)
            )->count(),
        ];

        $quota = (int) ($selectedTrip->quota ?? 0);
        $occupied = (int) ($selectedTrip->booked_seats ?? 0);
        $summary['quota'] = $quota;
        $summary['occupied'] = $occupied;
        $summary['occupancy'] = $quota > 0 ? (int) min(100, round(($occupied / $quota) * 100)) : 0;
        $summary['remaining'] = max(0, $quota - $occupied);

        $reminders = $all
            ->filter(fn (Booking $b) => $b->can_check_in && ! $b->is_checked_in)
            ->map(fn (Booking $b) => [
                'name' => $b->customer_name,
                'code' => $b->booking_code,
                'url' => $this->whatsappUrl($b, $selectedTrip),
            ])
            ->values();

        return view('admin.manifest.index', compact(
            'trips', 'selectedTrip', 'passengers', 'summary', 'filter', 'search', 'reminders'
        ));
    }

    public function checkIn(Request $request, Booking $booking): RedirectResponse
    {
        $request->validate(['action' => ['nullable', 'in:in,out']]);

        $back = fn () => redirect()->route('admin.manifest.index', array_filter([
            'trip_id' => $booking->trip_id,
            'filter' => $request->input('filter'),
            'q' => $request->input('q'),
        ]));

        $action = $request->input('action', $booking->is_checked_in ? 'out' : 'in');

        if ($action === 'out') {
            $booking->forceFill(['checked_in_at' => null, 'checked_in_by' => null])->save();

            return $back()->with('success', "Check-in {$booking->customer_name} dibatalkan.");
        }

        if (! $booking->can_check_in) {
            return $back()->with(
                'error',
                "Check-in ditolak: {$booking->customer_name} belum membayar DP/lunas atau booking belum terkonfirmasi."
            );
        }

        $booking->forceFill([
            'checked_in_at' => now(),
            'checked_in_by' => $request->user()->id,
        ])->save();

        return $back()->with('success', "{$booking->customer_name} ({$booking->pax} pax) berhasil check-in.");
    }

    public function storePassenger(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trip_id' => ['required', 'exists:trips,id'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'pax' => ['required', 'integer', 'min:1', 'max:50'],
            'pickup_location' => ['nullable', 'string', 'max:200'],
            'payment_status' => ['required', 'in:paid,partial'],
            'notes' => ['nullable', 'string', 'max:500'],
            'check_in_now' => ['nullable', 'boolean'],
        ]);

        $booking = DB::transaction(function () use ($validated, $request) {
            $trip = Trip::with('package')->lockForUpdate()->findOrFail($validated['trip_id']);

            if (in_array($trip->status, ['cancelled', 'completed'], true)) {
                throw ValidationException::withMessages([
                    'trip_id' => 'Trip ini sudah ' . strtolower($trip->status_label) . ' dan tidak dapat menerima penumpang baru.',
                ]);
            }

            $pax = (int) $validated['pax'];
            if ($pax > $trip->remaining_seats) {
                throw ValidationException::withMessages([
                    'pax' => "Kursi tidak cukup. Sisa kursi trip ini hanya {$trip->remaining_seats} pax.",
                ]);
            }

            $total = (int) ($trip->package->price ?? 0) * $pax;
            $paidNow = $validated['payment_status'] === 'paid' ? $total : (int) round($total * 0.30);
            $checkInNow = $request->boolean('check_in_now');

            $booking = new Booking();
            $booking->forceFill([
                'booking_code' => 'DJT-' . Str::upper(Str::random(8)),
                'user_id' => null,
                'trip_id' => $trip->id,
                'package_id' => $trip->package_id,
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? '-',
                'customer_phone' => $validated['customer_phone'],
                'pax' => $pax,
                'pickup_location' => $validated['pickup_location'] ?? null,
                'total_price' => $total,
                'down_payment' => $paidNow,
                'payment_method' => 'Tunai (Loket Offline)',
                'payment_status' => $validated['payment_status'],
                'booking_status' => 'confirmed',
                'checked_in_at' => $checkInNow ? now() : null,
                'checked_in_by' => $checkInNow ? $request->user()->id : null,
                'notes' => $validated['notes'] ?? null,
            ])->save();

            $booking->transactions()->create([
                'transaction_code' => 'TRX-' . Str::upper(Str::random(10)),
                'amount' => $paidNow,
                'payment_method' => 'Tunai (Loket Offline)',
                'payment_channel' => 'Kasir ' . $request->user()->name,
                'paid_at' => now(),
                'status' => 'success',
                'payment_stage' => $validated['payment_status'] === 'paid' ? 'pelunasan' : 'dp',
            ]);

            $trip->booked_seats = (int) $trip->booked_seats + $pax;
            $trip->status = $trip->resolveStatus();
            $trip->save();

            return $booking;
        });

        return redirect()
            ->route('admin.manifest.index', ['trip_id' => $booking->trip_id])
            ->with('success', "Penumpang {$booking->customer_name} ({$booking->pax} pax) berhasil ditambahkan ke manifes.");
    }

    public function export(Request $request): StreamedResponse|RedirectResponse
    {
        $trip = Trip::with('package')->find($request->query('trip_id'));

        if (! $trip) {
            return redirect()->route('admin.manifest.index')->with('error', 'Pilih trip terlebih dahulu untuk mengekspor manifes.');
        }

        $passengers = $this->manifestQuery($trip)->get();
        $fileName = 'Manifes_' . Str::slug($trip->batch_name) . '_' . $trip->departure_date->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($passengers, $trip) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['Trip', $trip->batch_name]);
            fputcsv($out, ['Keberangkatan', $trip->departure_date->format('d/m/Y') . ($trip->departure_time ? ' ' . $trip->departure_time : '')]);
            fputcsv($out, []);
            fputcsv($out, ['No', 'Kode Booking', 'Nama', 'Pax', 'WhatsApp / HP', 'Email', 'Penjemputan', 'Status Bayar', 'Check-in', 'Catatan']);

            foreach ($passengers as $i => $b) {
                fputcsv($out, [
                    $i + 1,
                    $b->booking_code,
                    $b->customer_name,
                    $b->pax,
                    $b->customer_phone,
                    $b->customer_email,
                    $b->pickup_location,
                    $this->paymentLabel($b),
                    $b->checked_in_at ? $b->checked_in_at->format('d/m/Y H:i') : 'Belum hadir',
                    $b->notes,
                ]);
            }

            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ------------------------------------------------------------------

    private function manifestQuery(Trip $trip)
    {
        return Booking::with(['transactions', 'user'])
            ->where('trip_id', $trip->id)
            ->whereIn('booking_status', self::MANIFEST_STATUSES)
            ->orderBy('id');
    }

    private function resolveTrip(Request $request, $trips): ?Trip
    {
        if ($request->filled('trip_id')) {
            $found = Trip::with(['package', 'vehicle'])->find($request->query('trip_id'));
            if ($found) {
                return $found;
            }
        }

        $today = now()->startOfDay();
        $pick = $trips->first(fn (Trip $t) => $t->departure_date->gte($today)) ?? $trips->last();

        return $pick ? Trip::with(['package', 'vehicle'])->find($pick->id) : null;
    }

    private function paymentLabel(Booking $b): string
    {
        return match ($b->payment_status) {
            'paid' => 'Lunas',
            'partial' => 'Bayar DP',
            'refunded' => 'Refund',
            default => 'Menunggu Pembayaran',
        };
    }

    private function whatsappUrl(Booking $b, Trip $trip): string
    {
        $phone = preg_replace('/\D+/', '', (string) $b->customer_phone);
        if (Str::startsWith($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $time = $trip->departure_time ?: '-';
        $point = $trip->meeting_point ?: 'titik kumpul yang telah diinformasikan';
        $message = "Halo {$b->customer_name}, pengingat keberangkatan {$trip->batch_name} pada "
            . $trip->departure_date->translatedFormat('d F Y')
            . " pukul {$time} di {$point}. Kode booking: {$b->booking_code}. Terima kasih - D'Java Tour & Travel.";

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }
}
