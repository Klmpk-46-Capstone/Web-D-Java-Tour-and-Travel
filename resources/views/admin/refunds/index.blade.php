@extends('layouts.admin')

@section('title', 'Refund & Pembatalan Tiket')

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-teal-700">OPERASIONAL PUSAT</a>
                <span>/</span>
                <span class="text-teal-700">RESOLUSI &amp; KEUANGAN</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span>Refund &amp; Pembatalan</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-rose-100 text-rose-700">
                    {{ $pendingCount }} Pengajuan Aktif
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Validasi klaim pembatalan tiket. Persetujuan admin otomatis mengembalikan kursi ke kuota trip dan mengupdate histori customer.
            </p>
        </div>
    </div>

    {{-- SUMMARY METRICS --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-rose-200/80 p-5 shadow-2xs">
            <div class="text-xs font-extrabold text-rose-700 uppercase tracking-wider mb-1">Tiket Ditahan (Perlu Review)</div>
            <div class="text-3xl font-black text-rose-600 my-1">{{ $pendingCount }} Kasus</div>
            <div class="text-xs text-slate-500">Nominal Disengketakan: <strong class="text-slate-900">Rp {{ number_format($totalDisputed, 0, ',', '.') }}</strong></div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="text-xs font-extrabold text-emerald-700 uppercase tracking-wider mb-1">Refund Disetujui (Selesai)</div>
            <div class="text-3xl font-black text-emerald-600 my-1">{{ $approvedCount }} Kasus</div>
            <div class="text-xs text-slate-500">Dana telah ditransfer &amp; kursi dipulihkan</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs">
            <div class="text-xs font-extrabold text-slate-400 uppercase tracking-wider mb-1">Pengajuan Ditolak</div>
            <div class="text-3xl font-black text-slate-700 my-1">{{ $rejectedCount }} Kasus</div>
            <div class="text-xs text-slate-500">Sesuai klausul batas waktu pembatalan</div>
        </div>
    </div>

    {{-- REFUND REQUESTS TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Antrean Klaim Refund Pelanggan</h3>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.refunds.index') }}" class="px-2.5 py-1 text-xs rounded-lg font-bold {{ !request('status') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Semua</a>
                <a href="{{ route('admin.refunds.index', ['status' => 'pending']) }}" class="px-2.5 py-1 text-xs rounded-lg font-bold {{ request('status') === 'pending' ? 'bg-rose-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Pending ({{ $pendingCount }})</a>
                <a href="{{ route('admin.refunds.index', ['status' => 'approved']) }}" class="px-2.5 py-1 text-xs rounded-lg font-bold {{ request('status') === 'approved' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">Disetujui</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-4">KODE REFUND &amp; BOOKING</th>
                        <th class="py-3 px-4">PELANGGAN</th>
                        <th class="py-3 px-4">PAKET &amp; JADWAL</th>
                        <th class="py-3 px-4">NOMINAL REFUND</th>
                        <th class="py-3 px-4">ALASAN KLAIM</th>
                        <th class="py-3 px-4 text-center">STATUS</th>
                        <th class="py-3 px-4 text-right">TINDAKAN ADMIN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($refunds as $rf)
                        @php
                            $b = $rf->booking;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 font-mono">
                                <div class="font-bold text-rose-600">#{{ $rf->refund_code }}</div>
                                <div class="text-[11px] text-teal-700">Booking #{{ $b->booking_code ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $b->customer_name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $b->customer_phone ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800">{{ $b->package->name ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $b->pax ?? 1 }} Pax • {{ $b->trip && $b->trip->departure_date ? $b->trip->departure_date->format('d M Y') : '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($rf->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 max-w-xs text-slate-600 leading-relaxed">
                                {{ $rf->reason }}
                                @if ($rf->admin_notes)
                                    <div class="mt-1 text-[11px] text-teal-800 bg-teal-50 p-1.5 rounded">
                                        <strong>Catatan Admin:</strong> {{ $rf->admin_notes }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($rf->status === 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 animate-pulse">
                                        Perlu Review
                                    </span>
                                @elseif ($rf->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                        Disetujui
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600">
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                @if ($rf->status === 'pending')
                                    <div class="inline-flex items-center gap-2">
                                        <form action="{{ route('admin.refunds.approve', $rf->id) }}" method="POST" onsubmit="return confirm('Setujui refund ini? Kuota kursi pada trip terkait akan otomatis dipulihkan.');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold transition shadow-xs cursor-pointer">
                                                ✓ Setujui &amp; Pulihkan Kursi
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.refunds.reject', $rf->id) }}" method="POST" onsubmit="return confirm('Tolak pengajuan refund ini?');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold transition cursor-pointer">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">Telah Diarsip</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Tidak ada data pengajuan refund.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($refunds->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $refunds->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
