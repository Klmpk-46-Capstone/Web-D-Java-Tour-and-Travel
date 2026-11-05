<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Services\FleetAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ArmadaController extends Controller
{
    public function __construct(private readonly FleetAvailability $fleet)
    {
    }

    public function armada(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'kategori' => ['nullable', 'in:mpv,premium,minibus'],
            'transmisi' => ['nullable', 'in:at,mt'],
            'urutkan' => ['nullable', 'in:rekomendasi,harga_terendah,harga_tertinggi,kapasitas'],
        ]);

        $q = trim((string) ($filters['q'] ?? ''));
        $category = $filters['kategori'] ?? null;
        $transmission = $filters['transmisi'] ?? null;
        $sort = $filters['urutkan'] ?? 'rekomendasi';

        $query = Vehicle::query()
            ->where('is_active', true)
            ->whereHas('activeUnits')
            ->when($q !== '', function ($query) use ($q) {
                $keyword = '%' . $q . '%';

                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('name', 'like', $keyword)
                        ->orWhere('category', 'like', $keyword)
                        ->orWhere('badge', 'like', $keyword)
                        ->orWhere('transmission', 'like', $keyword)
                        ->orWhere('description', 'like', $keyword);
                });
            })
            ->when(filled($category), fn ($query) => $query->where('category', $category))
            ->when($transmission === 'at', fn ($query) => $query->where('transmission', 'like', '%matic%'))
            ->when($transmission === 'mt', fn ($query) => $query->where('transmission', 'like', '%manual%'));

        $query = match ($sort) {
            'harga_terendah' => $query->orderBy('price_per_day'),
            'harga_tertinggi' => $query->orderByDesc('price_per_day'),
            'kapasitas' => $query->orderByDesc('seats'),
            default => $query->orderBy('price_per_day')->orderBy('id'),
        };

        $vehicles = $query->paginate(9)->withQueryString();

        $isEnglish = app()->getLocale() === 'en';

        $categories = [
            'mpv' => $isEnglish ? 'Family MPV' : 'MPV Keluarga',
            'premium' => $isEnglish ? 'Premium & Hybrid' : 'Premium & Hybrid',
            'minibus' => $isEnglish ? 'Group Minibus' : 'Minibus Rombongan',
        ];

        return view($this->dashboardView('armada.index'), compact(
            'vehicles',
            'categories',
            'category',
            'transmission',
            'q',
            'sort'
        ));
    }

    // Customer Vehicle Detail: spesifikasi, opsi sewa (dengan/tanpa supir), dan unit terkait.

    public function armadaDetail(Request $request, Vehicle $vehicle): View
    {
        abort_unless($vehicle->is_active && $vehicle->activeUnits()->exists(), 404);

        $validated = $request->validate([
            'rental_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $availabilityDate = isset($validated['rental_date'])
            ? Carbon::parse($validated['rental_date'])->startOfDay()
            : Carbon::today();

        $relatedVehicles = Vehicle::query()
            ->active()
            ->whereHas('activeUnits')
            ->whereKeyNot($vehicle->getKey())
            ->orderByRaw('CASE WHEN category = ? THEN 0 ELSE 1 END', [$vehicle->category])
            ->orderBy('price_per_day')
            ->limit(3)
            ->get();

        $reviews = $vehicle->reviews()->latest('id')->limit(6)->get();
        $averageRating = $reviews->isNotEmpty()
            ? round((float) $reviews->avg('rating'), 1)
            : 0;

        return view($this->dashboardView('armada.detail'), [
            'vehicle' => $vehicle,
            'relatedVehicles' => $relatedVehicles,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'availabilityDate' => $availabilityDate,
            'availableUnitsCount' => $this->fleet->countAvailable($vehicle, $availabilityDate, $availabilityDate),
            'totalUnitsCount' => $this->fleet->countTotal($vehicle),
        ]);
    }

    public function availability(Request $request, Vehicle $vehicle)
    {
        abort_unless($vehicle->is_active, 404);

        $validated = $request->validate([
            'rental_date' => ['required', 'date', 'after_or_equal:today'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $start = Carbon::parse($validated['rental_date'])->startOfDay();
        $duration = (int) ($validated['duration'] ?? 1);
        $end = $start->copy()->addDays($duration - 1);

        return response()->json([
            'rental_date' => $start->toDateString(),
            'duration' => $duration,
            'available' => $this->fleet->countAvailable($vehicle, $start, $end),
            'total' => $this->fleet->countTotal($vehicle),
        ]);
    }

    // Customer Vehicle Booking Form: mempertahankan unit, opsi sewa, tanggal, dan pax dari halaman detail.

    public function armadaBookingForm(Request $request, Vehicle $vehicle): View|RedirectResponse
    {
        abort_unless($vehicle->is_active && $vehicle->activeUnits()->exists(), 404);

        $options = collect($vehicle->rentalOptions())->values();
        $optionKeys = $options->pluck('key')->all();

        $validated = $request->validate([
            'rental_date' => ['nullable', 'date', 'after_or_equal:today'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:30'],
            'pax' => ['nullable', 'integer', 'min:1', 'max:' . $vehicle->seats],
            'rental_option' => ['nullable', 'string', Rule::in($optionKeys)],
        ]);

        $selectedOptionKey = $validated['rental_option'] ?? ($optionKeys[0] ?? null);
        $selectedOption = $options->firstWhere('key', $selectedOptionKey) ?? $options->first();
        $selectedRentalDate = $validated['rental_date'] ?? null;
        $selectedDuration = (int) ($validated['duration'] ?? 1);

        if ($selectedRentalDate) {
            $start = Carbon::parse($selectedRentalDate)->startOfDay();
            $end = $start->copy()->addDays($selectedDuration - 1);

            if ($this->fleet->countAvailable($vehicle, $start, $end) === 0) {
                return redirect()->to(localized_route('armada.show', [
                    'vehicle' => $vehicle->slug,
                    'rental_date' => $start->toDateString(),
                ]));
            }
        }

        return view($this->dashboardView('armada.formulir.index'), [
            'vehicle' => $vehicle,
            'options' => $options->all(),
            'selectedOption' => $selectedOption,
            'selectedOptionKey' => $selectedOptionKey,
            'selectedRentalDate' => $validated['rental_date'] ?? null,
            'selectedDuration' => (int) ($validated['duration'] ?? 1),
            'selectedPax' => (int) ($validated['pax'] ?? 1),
        ]);
    }

    // Customer Vehicle Booking Store: memvalidasi, menghitung harga di server, menyimpan ke database, dan mengarah ke pembayaran.

    public function storeBooking(Request $request, Vehicle $vehicle)
    {
        abort_unless($vehicle->is_active, 404);

        $options = collect($vehicle->rentalOptions())->values();
        $optionKeys = $options->pluck('key')->all();

        $validated = $request->validate([
            'rental_date' => ['required', 'date', 'after_or_equal:today'],
            'duration' => ['required', 'integer', 'min:1', 'max:30'],
            'pax' => ['required', 'integer', 'min:1', 'max:' . $vehicle->seats],
            'rental_option' => ['required', 'string', Rule::in($optionKeys)],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_email' => ['required', 'email', 'max:150'],
            'delivery_method' => ['nullable', 'string', 'in:self_pickup,delivery'],
            'pickup_location' => ['nullable', 'string', 'max:200'],
            'pickup_details' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
            'identity_file' => ['nullable', 'file', 'mimes:jpeg,png,pdf', 'max:2048'],
        ]);

        $selectedOption = $options->firstWhere('key', $validated['rental_option']) ?? $options->first();
        $rate = (int) ($selectedOption['price'] ?? 0);
        $duration = (int) $validated['duration'];
        $totalPrice = $rate * $duration;
        $downPaymentRate = 30;
        $downPayment = (int) round($totalPrice * $downPaymentRate / 100);

        $start = Carbon::parse($validated['rental_date'])->startOfDay();
        $end = $start->copy()->addDays($duration - 1);
        $bookingCode = 'BK-' . strtoupper(Str::random(8));

        $booking = DB::transaction(function () use (
            $request,
            $vehicle,
            $validated,
            $duration,
            $rate,
            $totalPrice,
            $downPayment,
            $bookingCode,
            $start,
            $end
        ) {
            $unit = $this->fleet->allocate($vehicle, $start, $end);

            if (! $unit) {
                $totalUnits = $this->fleet->countTotal($vehicle);

                throw ValidationException::withMessages([
                    'rental_date' => $totalUnits === 0
                        ? 'Belum ada unit terdaftar untuk tipe armada ini. Silakan hubungi admin.'
                        : 'Semua unit untuk tipe armada ini sudah terpakai pada tanggal tersebut. Silakan pilih tanggal lain.',
                ]);
            }

            $identityPath = null;
            if ($request->hasFile('identity_file')) {
                $identityPath = $request->file('identity_file')->store('identities', 'public');
            }

            return Booking::create([
                'booking_code' => $bookingCode,
                'user_id' => $request->user()->id,
                'vehicle_id' => $vehicle->id,
                'vehicle_unit_id' => $unit->id,
                'rental_date' => $validated['rental_date'],
                'duration' => $duration,
                'pax' => $validated['pax'],
                'rental_option' => $validated['rental_option'],
                'rate' => $rate,
                'total_price' => $totalPrice,
                'down_payment' => $downPayment,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $validated['customer_email'],
                'delivery_method' => $validated['delivery_method'] ?? 'self_pickup',
                'pickup_location' => $validated['pickup_location'] ?? null,
                'pickup_details' => $validated['pickup_details'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'identity_file_path' => $identityPath,
                'selected_armada' => $vehicle->name,
                'payment_method' => 'Xendit',
                'payment_status' => 'pending',
                'booking_status' => 'pending',
            ]);
        });

        return redirect()->to(localized_route('pembayaran.mulai', [
            'bookingCode' => $booking->booking_code,
        ]))->with(
            'success',
            app()->getLocale() === 'en'
                ? 'Your vehicle booking was created. Booking code: ' . $booking->booking_code
                : 'Reservasi armada berhasil dibuat. Kode booking: ' . $booking->booking_code
        );
    }

    // Customer Package Detail with Itinerary, Dynamic Schedule/Quota & Reviews
}
