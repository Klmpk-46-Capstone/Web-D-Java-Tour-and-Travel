<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCustomerController extends Controller
{
    /**
     * Tampilkan daftar akun customer terpusat dengan metrik & status keaktifan.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', 'all');

        $query = User::withCount('bookings')
            ->where('role', 'customer');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('whatsapp', 'like', "%{$search}%");
            });
        }

        if ($status === 'blacklisted') {
            $query->where('is_blacklisted', true);
        } elseif ($status === 'active') {
            $query->where('is_blacklisted', false);
        } elseif ($status === 'online') {
            $query->where('is_blacklisted', false)
                  ->where(function ($q) {
                      $q->where('last_seen_at', '>=', now()->subMinutes(15))
                        ->orWhere('last_login_at', '>=', now()->subMinutes(15));
                  });
        }

        $customers = $query->latest('id')->paginate(15)->withQueryString();

        // Metrik statistik pelanggan
        $baseQuery = User::where('role', 'customer');
        $totalCustomers = (clone $baseQuery)->count();
        $blacklistedCount = (clone $baseQuery)->where('is_blacklisted', true)->count();
        $activeCount = (clone $baseQuery)->where('is_blacklisted', false)->count();
        $onlineCount = (clone $baseQuery)
            ->where('is_blacklisted', false)
            ->where(function ($q) {
                $q->where('last_seen_at', '>=', now()->subMinutes(15))
                  ->orWhere('last_login_at', '>=', now()->subMinutes(15));
            })->count();

        return view('admin.customers.index', compact(
            'customers',
            'search',
            'status',
            'totalCustomers',
            'blacklistedCount',
            'activeCount',
            'onlineCount'
        ));
    }

    /**
     * Halaman detail profil, riwayat reservasi transparan, dan rekam jejak aktivitas customer.
     */
    public function show(User $customer): View
    {
        // Pastikan hanya role customer yang dikelola di modul ini
        if ($customer->role !== 'customer') {
            abort(404, 'Data customer tidak ditemukan.');
        }

        // Ambil seluruh riwayat booking milik customer (murni berdasarkan user_id akun ini)
        $bookings = $customer->bookings()
            ->with(['trip.package', 'trip.vehicle', 'package', 'transactions'])
            ->latest('id')
            ->get();

        // Metrik finansial & reservasi customer
        $totalBookings = $bookings->count();
        $completedBookings = $bookings->where('booking_status', 'completed')->count();
        $activeBookings = $bookings->whereIn('booking_status', ['pending', 'confirmed'])->count();
        $cancelledBookings = $bookings->whereIn('booking_status', ['cancelled', 'refund_requested'])->count();
        $totalPax = (int) $bookings->sum('pax');

        // Akumulasi total pembayaran sukses
        $totalSpent = 0;
        foreach ($bookings as $b) {
            if ($b->transactions->isNotEmpty()) {
                $totalSpent += $b->transactions->where('status', 'success')->sum('amount');
            } elseif (in_array($b->payment_status, ['paid', 'partial'], true)) {
                $totalSpent += ($b->payment_status === 'paid' ? $b->total_price : $b->down_payment);
            }
        }

        // Susun feed aktivitas kronologis
        $activities = collect();

        // 1. Akun dibuat
        if ($customer->created_at) {
            $activities->push((object) [
                'type' => 'account_created',
                'title' => 'Akun Terdaftar',
                'description' => $customer->google_id
                    ? 'Customer mendaftar menggunakan integrasi Google OAuth.'
                    : 'Customer mendaftar secara mandiri melalui formulir registrasi website.',
                'badge' => 'Pendaftaran',
                'badge_color' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                'date' => $customer->created_at,
            ]);
        }

        // 2. Login terakhir
        if ($customer->last_login_at) {
            $activities->push((object) [
                'type' => 'login',
                'title' => 'Sesi Login Terakhir',
                'description' => 'Customer berhasil masuk (login) ke sistem website D\'Java Tour & Travel.',
                'badge' => 'Autentikasi',
                'badge_color' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                'date' => $customer->last_login_at,
            ]);
        }

        // 3. Online / aktivitas terakhir
        if ($customer->last_seen_at && (!$customer->last_login_at || $customer->last_seen_at->gt($customer->last_login_at))) {
            $activities->push((object) [
                'type' => 'last_seen',
                'title' => 'Aktivitas Halaman Terakhir',
                'description' => 'Customer terpantau aktif menelusuri katalog atau bertransaksi pada sistem.',
                'badge' => 'Sesi Aktif',
                'badge_color' => 'bg-teal-50 text-teal-700 ring-teal-600/20',
                'date' => $customer->last_seen_at,
            ]);
        }

        // 4. Riwayat Booking & Reservasi
        foreach ($bookings as $bk) {
            $pkgName = $bk->trip?->package?->name ?? $bk->package?->name ?? 'Paket Wisata';
            $activities->push((object) [
                'type' => 'booking',
                'title' => "Reservasi #{$bk->booking_code} Dibuat",
                'description' => "Membuat reservasi untuk '{$pkgName}' ({$bk->pax} Pax). Total Rp " . number_format($bk->total_price, 0, ',', '.') . " (" . ucfirst($bk->booking_status) . ").",
                'badge' => 'Reservasi',
                'badge_color' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                'date' => $bk->created_at,
            ]);

            // Riwayat transaksi pembayaran tiap booking
            foreach ($bk->transactions as $trx) {
                if ($trx->paid_at || $trx->status === 'success') {
                    $activities->push((object) [
                        'type' => 'payment',
                        'title' => "Pembayaran {$trx->transaction_code}",
                        'description' => "Pembayaran {$trx->payment_stage} senilai Rp " . number_format($trx->amount, 0, ',', '.') . " via {$trx->payment_method} ({$trx->status}).",
                        'badge' => 'Transaksi',
                        'badge_color' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                        'date' => $trx->paid_at ?? $trx->created_at,
                    ]);
                }
            }
        }

        // 5. Riwayat Ulasan
        $reviews = $customer->reviews()->latest()->get();
        foreach ($reviews as $rev) {
            $activities->push((object) [
                'type' => 'review',
                'title' => "Ulasan Bintang {$rev->rating}/5 Dikirim",
                'description' => "Customer memberikan testimoni ulasan: \"{$rev->comment}\"",
                'badge' => 'Ulasan',
                'badge_color' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                'date' => $rev->created_at,
            ]);
        }

        // 6. Riwayat Blacklist
        if ($customer->is_blacklisted) {
            $activities->push((object) [
                'type' => 'blacklist',
                'title' => 'Akun Dimasukkan ke Daftar Hitam (Blacklist)',
                'description' => 'Administrator membatasi akses akun ini. Alasan: ' . ($customer->blacklist_reason ?: 'Pelanggaran ketentuan operasional.'),
                'badge' => 'Keamanan',
                'badge_color' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                'date' => $customer->blacklisted_at ?? now(),
            ]);
        }

        // Urutkan kronologis dari paling baru
        $activities = $activities->sortByDesc('date')->values();

        return view('admin.customers.show', compact(
            'customer',
            'bookings',
            'activities',
            'totalBookings',
            'completedBookings',
            'activeBookings',
            'cancelledBookings',
            'totalPax',
            'totalSpent'
        ));
    }

    /**
     * Kontrol Keamanan: Blacklist / Un-blacklist akun customer.
     */
    public function toggleBlacklist(Request $request, User $customer): RedirectResponse
    {
        if ($customer->role !== 'customer') {
            return back()->with('error', 'Hanya akun customer yang dapat di-blacklist.');
        }

        if ($customer->is_blacklisted) {
            // Un-blacklist
            $customer->forceFill([
                'is_blacklisted' => false,
                'blacklist_reason' => null,
                'blacklisted_at' => null,
            ])->save();

            return back()->with('success', "Status blacklist pada akun {$customer->name} berhasil dicabut. Akun kembali normal.");
        }

        // Lakukan Blacklist
        $validated = $request->validate([
            'blacklist_reason' => ['required', 'string', 'max:500'],
        ], [
            'blacklist_reason.required' => 'Wajib memberikan keterangan/alasan penonaktifan akun.',
        ]);

        $customer->forceFill([
            'is_blacklisted' => true,
            'blacklist_reason' => $validated['blacklist_reason'],
            'blacklisted_at' => now(),
        ])->save();

        return back()->with('success', "Akun customer {$customer->name} berhasil dinonaktifkan (Masuk Daftar Hitam / Blacklist).");
    }

    /**
     * Kontrol Penuh: Perbarui data profil customer oleh admin.
     * Catatan: Nomor identitas (KTP/Paspor) tidak dapat diubah oleh admin.
     */
    public function update(Request $request, User $customer): RedirectResponse
    {
        if ($customer->role !== 'customer') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($customer->id)],
            'whatsapp' => ['nullable', 'string', 'max:30'],
        ]);

        $customer->update([
            'name' => $validated['name'],
            'email' => Str::lower(trim($validated['email'])),
            'whatsapp' => $validated['whatsapp'] ?? null,
        ]);

        return back()->with('success', "Data kontak profil customer {$customer->name} berhasil diperbarui.");
    }

    /**
     * Kontrol Keamanan: Hapus akun customer dari sistem.
     */
    public function destroy(User $customer): RedirectResponse
    {
        if ($customer->role !== 'customer') {
            return back()->with('error', 'Tidak dapat menghapus akun non-customer.');
        }

        $name = $customer->name;

        DB::transaction(function () use ($customer) {
            // Lepaskan relasi pada booking agar data manifes & laporan keuangan masa lalu tidak hilang/rusak
            Booking::where('user_id', $customer->id)->update([
                'user_id' => null,
                'customer_email' => 'archived_' . time() . '_' . Str::lower($customer->email),
            ]);

            // Hapus ulasan
            $customer->reviews()->delete();

            // Hapus akun customer
            $customer->delete();
        });

        return redirect()
            ->route('admin.customers.index')
            ->with('success', "Akun customer {$name} berhasil dihapus dari sistem.");
    }
}
