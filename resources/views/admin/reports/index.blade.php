@extends('layouts.admin')

@section('title', 'Laporan Transaksi Keuangan')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700">OPERASIONAL PUSAT</a>
                <span>/</span>
                <span class="text-teal-700">FINANSIAL &amp; AUDIT</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Laporan Transaksi &amp; Pembayaran
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Audit riwayat pembayaran gateway, settlement perbankan, dan pengembalian dana (refund).
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a 
                href="{{ route('admin.reports.export-csv') }}" 
                class="inline-flex items-center gap-2 bg-teal-800 hover:bg-teal-900 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Format CSV
            </a>
        </div>
    </div>

    {{-- METRIC SUMMARY --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
            <span class="text-xs font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Total Pendapatan Terverifikasi</span>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 my-1">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <div class="text-xs text-teal-700 font-bold">100% Pembayaran Berhasil Masuk</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
            <span class="text-xs font-extrabold text-emerald-600 uppercase tracking-wider block mb-1">Transaksi Berhasil</span>
            <div class="text-2xl sm:text-3xl font-black text-emerald-700 my-1">
                {{ $successfulCount }} Transaksi
            </div>
            <div class="text-xs text-slate-500">Virtual Account &amp; QRIS Otomatis</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
            <span class="text-xs font-extrabold text-rose-600 uppercase tracking-wider block mb-1">Dana Dikembalikan (Refund)</span>
            <div class="text-2xl sm:text-3xl font-black text-rose-700 my-1">
                {{ $refundedCount }} Transaksi
            </div>
            <div class="text-xs text-slate-500">Status transaksi disesuaikan otomatis</div>
        </div>
    </div>

    {{-- TRANSACTIONS TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Log Transaksi Sistem</h3>
            <form action="{{ route('admin.reports.index') }}" method="GET" class="flex items-center gap-2">
                <select name="range" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none">
                    <option value="30" {{ request('range') == '30' ? 'selected' : '' }}>30 Hari Terakhir</option>
                    <option value="7" {{ request('range') == '7' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="all" {{ request('range') == 'all' ? 'selected' : '' }}>Semua Waktu</option>
                </select>

                <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Berhasil (Success)</option>
                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">KODE TRANSAKSI</th>
                        <th class="py-3 px-4">KODE BOOKING</th>
                        <th class="py-3 px-4">PELANGGAN</th>
                        <th class="py-3 px-4">PAKET WISATA</th>
                        <th class="py-3 px-4">METODE BAYAR</th>
                        <th class="py-3 px-4 text-right">TOTAL NOMINAL</th>
                        <th class="py-3 px-4 text-center">STATUS</th>
                        <th class="py-3 px-4">WAKTU BAYAR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($transactions as $tx)
                        @php $b = $tx->booking; @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                {{ $tx->transaction_code }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-teal-700 font-extrabold">
                                #{{ $b->booking_code ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800">{{ $b->customer_name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $b->customer_email ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                {{ $b && $b->package ? $b->package->name : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $tx->payment_method }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-extrabold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($tx->status === 'success')
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                        BERHASIL
                                    </span>
                                @elseif ($tx->status === 'refunded')
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold bg-rose-100 text-rose-700">
                                        REFUNDED
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold bg-slate-100 text-slate-700">
                                        {{ strtoupper($tx->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">
                                {{ $tx->paid_at ? $tx->paid_at->format('d/m/Y H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">
                                Tidak ada catatan transaksi pada filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
