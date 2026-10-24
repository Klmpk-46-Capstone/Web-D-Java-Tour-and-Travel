<div class="rounded-2xl border border-blue-100 bg-white px-6 py-14 text-center shadow-sm">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-teal-50 text-teal-700">
        <svg
            class="h-8 w-8"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
            aria-hidden="true"
        >
            <rect x="4" y="3" width="16" height="18" rx="2"></rect>
            <path d="M8 8h8M8 12h8M8 16h5"></path>
        </svg>
    </div>

    <h2 class="mt-5 text-xl font-bold text-slate-900">
        Tidak ada transaksi ditemukan
    </h2>

    <p class="mx-auto mt-2 max-w-lg text-base leading-7 text-slate-500">
        Belum ada reservasi pada kategori ini atau kata kunci pencarian tidak cocok dengan data transaksi Anda.
    </p>

    @if ($activeStatus !== 'all' || $search !== '')
        <a
            href="{{ route('customer.bookings.index') }}"
            class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-800"
        >
            Lihat semua transaksi
        </a>
    @endif
</div>
