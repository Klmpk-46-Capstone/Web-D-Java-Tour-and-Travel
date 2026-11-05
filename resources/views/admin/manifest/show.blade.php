@extends('layouts.admin')

@section('title', 'Detail Penumpang - ' . $passenger->name)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.manifest.index', ['trip_id' => $booking->trip_id]) }}" class="hover:text-teal-700">Manifes Penumpang</a>
                <span>/</span>
                <span class="text-teal-700">Detail Penumpang</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                {{ $passenger->name }}
                @if ($passenger->is_lead)
                    <span class="rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-extrabold text-teal-700 ring-1 ring-teal-600/20">Perwakilan Pemesan (PJ)</span>
                @else
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">Anggota Rombongan (Peserta {{ $index + 1 }}/{{ $pax }})</span>
                @endif
            </h1>
            <p class="text-xs text-slate-500 mt-1">Detail profil individu penumpang, verifikasi dokumen identitas, dan status reservasi.</p>
        </div>

        <a href="{{ route('admin.manifest.index', ['trip_id' => $booking->trip_id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 hover:bg-slate-50 transition shadow-2xs">
            &larr; Kembali ke Manifes
        </a>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-bold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs font-bold text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- KOLOM KIRI: FORM DATA & BERKAS IDENTITAS MANUAL --}}
        <div class="lg:col-span-7 space-y-6">
            <section class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
                <div class="flex items-center justify-between pb-3 mb-5 border-b border-slate-100">
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900">Kelola Data &amp; Dokumen Identitas</h2>
                        <p class="text-xs text-slate-500">Input manual atau perbarui berkas KTP/Paspor untuk penumpang offline.</p>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full {{ $passenger->identity_file_path ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ $passenger->identity_file_path ? '✓ Dokumen Terunggah' : 'Belum Ada Berkas' }}
                    </span>
                </div>

                <form action="{{ route('admin.manifest.passengers.update', ['booking' => $booking->id, 'index' => $index]) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Penumpang (Sesuai KTP / Paspor) <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $passenger->name) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-teal-600">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="identity_type" class="block text-xs font-bold text-slate-700 mb-1">Jenis Identitas</label>
                            <select id="identity_type" name="identity_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-teal-600">
                                <option value="KTP" {{ old('identity_type', $passenger->identity_type) === 'KTP' ? 'selected' : '' }}>KTP (Kartu Tanda Penduduk)</option>
                                <option value="Paspor" {{ old('identity_type', $passenger->identity_type) === 'Paspor' ? 'selected' : '' }}>Paspor Internasional</option>
                                <option value="SIM" {{ old('identity_type', $passenger->identity_type) === 'SIM' ? 'selected' : '' }}>SIM</option>
                                <option value="Lainnya" {{ old('identity_type', $passenger->identity_type) === 'Lainnya' ? 'selected' : '' }}>Lainnya / Identitas Pelajar</option>
                            </select>
                        </div>

                        <div>
                            <label for="identity_number" class="block text-xs font-bold text-slate-700 mb-1">Nomor Identitas (NIK / No. Paspor)</label>
                            <input type="text" id="identity_number" name="identity_number" value="{{ old('identity_number', $passenger->identity_number) }}" placeholder="Cth: 3578xxxxxxxxxxxx" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-teal-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">No. WhatsApp / Telepon</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $passenger->phone) }}" placeholder="Cth: 081234567890" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-teal-600">
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 mb-1">Alamat Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $passenger->email) }}" placeholder="Cth: nama@domain.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-teal-600">
                        </div>
                    </div>

                    {{-- DOKUMEN IDENTITAS & PRATINJAU --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Berkas Dokumen Identitas (Scan/Foto KTP/Paspor)</label>
                        
                        @if ($passenger->identity_file_path)
                            <div class="mb-3 p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/50 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <svg class="h-6 w-6 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 truncate">Berkas Tersimpan</p>
                                        <p class="text-[11px] text-slate-500 font-mono truncate">{{ basename($passenger->identity_file_path) }}</p>
                                    </div>
                                </div>

                                <a href="{{ route('admin.manifest.passengers.file', ['booking' => $booking->id, 'index' => $index]) }}" target="_blank" rel="noopener" class="px-3 py-1.5 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold transition shrink-0">
                                    Lihat Berkas
                                </a>
                            </div>
                        @endif

                        <div class="p-3.5 rounded-xl border border-dashed border-slate-300 bg-slate-50">
                            <input type="file" name="identity_file" accept="image/jpeg,image/png,application/pdf" class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-teal-700 file:text-white hover:file:bg-teal-800 cursor-pointer">
                            <p class="mt-2 text-[11px] text-slate-500">Unggah foto atau scan identitas baru (format JPG, PNG, atau PDF, maks. 2 MB).</p>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-xs font-bold text-slate-700 mb-1">Catatan Khusus / Kebutuhan Penumpang</label>
                        <textarea id="notes" name="notes" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-teal-600" placeholder="Cth: Alergi makanan laut, request tempat duduk depan, dsb.">{{ old('notes', $passenger->notes) }}</textarea>
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold transition shadow-sm cursor-pointer">
                            Simpan Perubahan Identitas
                        </button>
                    </div>
                </form>
            </section>
        </div>

        {{-- KOLOM KANAN: DETAIL TRIP & RESERVASI --}}
        <div class="lg:col-span-5 space-y-6">

            <section class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-4">
                <h3 class="text-sm font-extrabold text-slate-900 pb-2 border-b border-slate-100">Informasi Trip &amp; Jadwal</h3>

                <dl class="space-y-3 text-xs">
                    <div>
                        <dt class="text-[10px] uppercase font-bold text-slate-400">Paket Wisata / Rute</dt>
                        <dd class="font-bold text-slate-800 text-sm mt-0.5">{{ $booking->trip?->package?->name ?? 'Trip Wisata' }}</dd>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <dt class="text-[10px] uppercase font-bold text-slate-400">Keberangkatan</dt>
                            <dd class="font-extrabold text-slate-800 mt-0.5">{{ $booking->trip?->departure_date?->translatedFormat('d F Y') }}</dd>
                            @if ($booking->trip?->departure_time)<dd class="text-slate-500 font-mono">Pukul {{ $booking->trip->departure_time }}</dd>@endif
                        </div>
                        <div>
                            <dt class="text-[10px] uppercase font-bold text-slate-400">Kepulangan</dt>
                            <dd class="font-extrabold text-slate-800 mt-0.5">{{ $booking->trip?->return_date ? $booking->trip->return_date->translatedFormat('d F Y') : '-' }}</dd>
                        </div>
                    </div>

                    <div>
                        <dt class="text-[10px] uppercase font-bold text-slate-400">Titik Kumpul / Penjemputan</dt>
                        <dd class="font-bold text-slate-800 mt-0.5">{{ $booking->pickup_location ?: 'Titik Kumpul Utama' }}</dd>
                        @if ($booking->pickup_details)
                            <dd class="text-slate-500 text-[11px] mt-0.5">{{ $booking->pickup_details }}</dd>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1 border-t border-slate-100">
                        <div>
                            <dt class="text-[10px] uppercase font-bold text-slate-400">Armada</dt>
                            <dd class="font-bold text-slate-800 mt-0.5">{{ $booking->trip?->vehicle?->name ?? 'Belum ditentukan' }}</dd>
                            <dd class="font-mono text-[10px] font-extrabold text-slate-500 mt-0.5">
                                {{ $booking->trip?->vehicleUnit?->plate_number ?? 'Plat belum dialokasikan' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[10px] uppercase font-bold text-slate-400">Driver / Tour Leader</dt>
                            <dd class="font-bold text-slate-800 mt-0.5">{{ $booking->trip?->driver_name ?? 'Belum ditentukan' }}</dd>
                        </div>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-4">
                <h3 class="text-sm font-extrabold text-slate-900 pb-2 border-b border-slate-100">Status Reservasi &amp; Kehadiran</h3>

                <dl class="space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Kode Booking</dt>
                        <dd class="font-mono font-bold text-teal-700">#{{ $booking->booking_code }}</dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Status Pembayaran</dt>
                        <dd class="font-bold text-slate-800 uppercase">{{ $booking->payment_status }}</dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Status Check-in</dt>
                        <dd>
                            @if ($booking->is_checked_in)
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">✓ Sudah Hadir</span>
                            @else
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-700">Belum Hadir</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <form action="{{ route('admin.manifest.check-in', $booking) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="{{ $booking->is_checked_in ? 'out' : 'in' }}">
                        @if ($booking->is_checked_in)
                            <button type="submit" class="px-4 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition">
                                Batalkan Check-in
                            </button>
                        @elseif ($booking->can_check_in)
                            <button type="submit" class="px-4 py-2 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold transition">
                                Verifikasi Hadir (Check-in)
                            </button>
                        @endif
                    </form>
                </div>
            </section>

            {{-- ANGGOTA LAIN DALAM ROMBONGAN INI --}}
            @if ($pax > 1)
                <section class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs space-y-3">
                    <h3 class="text-sm font-extrabold text-slate-900 pb-2 border-b border-slate-100">
                        Anggota Lain dalam Rombongan (Total {{ $pax }} Pax)
                    </h3>
                    <div class="space-y-2">
                        @foreach ($booking->participants_list as $pIdx => $otherP)
                            <a href="{{ route('admin.manifest.passengers.show', ['booking' => $booking->id, 'index' => $pIdx]) }}" class="flex items-center justify-between p-2.5 rounded-xl border {{ $pIdx === $index ? 'border-teal-500 bg-teal-50/50' : 'border-slate-100 hover:bg-slate-50' }} transition text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="grid h-6 w-6 place-items-center rounded-full bg-slate-200 text-[10px] font-bold text-slate-700">
                                        {{ $pIdx + 1 }}
                                    </span>
                                    <span class="font-bold text-slate-800">{{ $otherP['name'] }}</span>
                                    @if ($pIdx === 0)
                                        <span class="text-[10px] text-teal-700 font-extrabold">(PJ)</span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-teal-700 font-semibold">&rarr;</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

        </div>
    </div>

</div>
@endsection

