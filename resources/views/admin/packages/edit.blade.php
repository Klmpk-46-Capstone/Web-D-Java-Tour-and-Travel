@extends('layouts.admin')

@section('title', 'Edit Paket ' . $package->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider text-slate-400 uppercase mb-1">
                <a href="{{ route('admin.packages.index') }}" class="hover:text-teal-700">PAKET WISATA</a>
                <span>/</span>
                <span class="text-teal-700">EDIT</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                Edit Paket: {{ $package->name }}
            </h1>
        </div>

        <a href="{{ route('admin.packages.index') }}" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition">
            &larr; Kembali
        </a>
    </div>

    {{-- FORM CARD --}}
    <form action="{{ route('admin.packages.update', $package->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs space-y-6">
        @csrf
        @method('PUT')

        {{-- BASIC INFORMATION --}}
        <div>
            <h2 class="text-sm font-extrabold text-slate-900 mb-4 pb-2 border-b border-slate-100">Informasi Dasar Paket</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Paket Wisata</label>
                    <input type="text" name="name" value="{{ old('name', $package->name) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Wisata</label>
                    <select name="category" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category', $package->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Rute Wilayah</label>
                    <select name="route_type" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600">
                        <option value="bahari" {{ $package->route_type === 'bahari' ? 'selected' : '' }}>Bahari (Karimunjawa / Jepara)</option>
                        <option value="gunung" {{ $package->route_type === 'gunung' ? 'selected' : '' }}>Gunung (Bromo / Dieng / Ijen)</option>
                        <option value="heritage" {{ $package->route_type === 'heritage' ? 'selected' : '' }}>Heritage (Jogja / Borobudur)</option>
                    </select>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">Durasi Perjalanan</label>
                        <span class="text-[10px] font-semibold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-full border border-teal-200">
                            Otomatis dari Itinerary
                        </span>
                    </div>
                    <input type="text" id="duration_input" name="duration" value="{{ old('duration', $package->duration) }}" readonly class="w-full bg-slate-100 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 cursor-not-allowed select-none focus:outline-none" title="Dihitung otomatis berdasarkan susunan hari itinerary">
                    <p class="mt-1 text-[11px] text-slate-500">Durasi terkunci otomatis: bertambah &amp; berkurang mengikuti jumlah hari pada susunan itinerary di bawah.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Harga per Orang (IDR)</label>
                    <input type="number" name="price" value="{{ old('price', $package->price) }}" required min="0" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Badge Promosi (Opsional)</label>
                    <input type="text" name="badge" value="{{ old('badge', $package->badge) }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600">
                </div>

                @include('admin.packages._cover-image', ['package' => $package])

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Paket Wisata</label>
                    <textarea name="description" rows="3" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600">{{ old('description', $package->description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- VISIBILITY TOGGLES --}}
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $package->is_featured) ? 'checked' : '' }} class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500">
                <div>
                    <span class="text-xs font-extrabold text-slate-800">Tandai sebagai Rekomendasi Utama (Featured)</span>
                    <span class="text-[11px] text-slate-500 block">Tampil otomatis di banner promo dan highlight beranda depan.</span>
                </div>
            </label>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $package->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500">
                <div>
                    <span class="text-xs font-extrabold text-slate-800">Status Aktif</span>
                    <span class="text-[11px] text-slate-500 block">Dapat dicari dan dipesan customer.</span>
                </div>
            </label>
        </div>

        {{-- ITINERARY BUILDER --}}
        <div>
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Susunan Jadwal Perjalanan (Itinerary)</h2>
                    <p class="text-xs text-slate-500">Kelola rincian aktivitas per hari yang terintegrasi langsung ke halaman detail customer.</p>
                </div>
                <button type="button" onclick="addItineraryDay()" class="px-3 py-1.5 bg-teal-50 text-teal-800 hover:bg-teal-100 text-xs font-bold rounded-xl transition cursor-pointer">
                    + Tambah Hari
                </button>
            </div>

            <div id="itineraryContainer" class="space-y-3">
                @php
                    $currentItineraries = old('itineraries', $package->itineraries->map(fn($it) => [
                        'title' => $it->title,
                        'description' => $it->description,
                    ])->toArray());
                @endphp

                @forelse ($currentItineraries as $idx => $it)
                    <div class="itinerary-row p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="day-label text-xs font-extrabold text-teal-800">HARI {{ $idx + 1 }}</span>
                            @if ($idx > 0)
                                <button type="button" onclick="removeItineraryDay(this)" class="text-xs text-rose-500 hover:underline cursor-pointer">Hapus Hari</button>
                            @endif
                        </div>
                        <input type="text" name="itineraries[{{ $idx }}][title]" required value="{{ $it['title'] ?? '' }}" placeholder="Judul aktivitas hari ke-{{ $idx + 1 }}" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-teal-600">
                        <textarea name="itineraries[{{ $idx }}][description]" rows="2" placeholder="Rincian jam, lokasi, dan kegiatan..." class="w-full bg-white border border-slate-300 rounded-lg p-2.5 text-xs focus:outline-none focus:border-teal-600">{{ $it['description'] ?? '' }}</textarea>
                    </div>
                @empty
                    <div class="itinerary-row p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="day-label text-xs font-extrabold text-teal-800">HARI 1</span>
                        </div>
                        <input type="text" name="itineraries[0][title]" required value="Kedatangan, Check-in & Sunset View" placeholder="Judul aktivitas hari ini" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-teal-600">
                        <textarea name="itineraries[0][description]" rows="2" placeholder="Rincian jam, lokasi, dan kegiatan..." class="w-full bg-white border border-slate-300 rounded-lg p-2.5 text-xs focus:outline-none focus:border-teal-600">Penjemputan di meeting point, perjalanan menuju destinasi, check-in akomodasi, dan menikmati pemandangan sore hari.</textarea>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- BUTTON ACTIONS --}}
        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
            <a href="{{ route('admin.packages.index') }}" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition">Batal</a>
            <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-teal-700 hover:bg-teal-800 rounded-xl shadow-sm transition">
                Perbarui Paket &amp; Itinerary
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
    function calculateDurationText(dayCount) {
        if (dayCount <= 1) {
            return '1 Hari';
        }
        const nights = dayCount - 1;
        return `${dayCount} Hari ${nights} Malam`;
    }

    function updateDurationDisplay() {
        const count = document.querySelectorAll('#itineraryContainer .itinerary-row').length;
        const durationInput = document.getElementById('duration_input');
        if (durationInput) {
            durationInput.value = calculateDurationText(count);
        }
    }

    function reindexItineraries() {
        const container = document.getElementById('itineraryContainer');
        const rows = container.querySelectorAll('.itinerary-row');
        rows.forEach((row, index) => {
            const dayNumber = index + 1;
            const label = row.querySelector('.day-label');
            if (label) label.textContent = `HARI ${dayNumber}`;
            const titleInput = row.querySelector('input[type="text"]');
            if (titleInput) {
                titleInput.name = `itineraries[${index}][title]`;
                titleInput.placeholder = `Judul aktivitas hari ke-${dayNumber}`;
            }
            const descTextarea = row.querySelector('textarea');
            if (descTextarea) {
                descTextarea.name = `itineraries[${index}][description]`;
            }

            const headerDiv = row.querySelector('.flex.items-center.justify-between');
            let deleteBtn = row.querySelector('button[onclick*="removeItineraryDay"]');
            if (index === 0) {
                if (deleteBtn) deleteBtn.remove();
            } else if (!deleteBtn && headerDiv) {
                deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'text-xs text-rose-500 hover:underline cursor-pointer';
                deleteBtn.textContent = 'Hapus Hari';
                deleteBtn.setAttribute('onclick', 'removeItineraryDay(this)');
                headerDiv.appendChild(deleteBtn);
            }
        });

        updateDurationDisplay();
    }

    function removeItineraryDay(button) {
        const container = document.getElementById('itineraryContainer');
        if (container.querySelectorAll('.itinerary-row').length <= 1) {
            return;
        }
        button.closest('.itinerary-row').remove();
        reindexItineraries();
    }

    function addItineraryDay() {
        const container = document.getElementById('itineraryContainer');
        const index = container.querySelectorAll('.itinerary-row').length;
        const dayNumber = index + 1;
        const div = document.createElement('div');
        div.className = 'itinerary-row p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2';
        div.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="day-label text-xs font-extrabold text-teal-800">HARI ${dayNumber}</span>
                <button type="button" onclick="removeItineraryDay(this)" class="text-xs text-rose-500 hover:underline cursor-pointer">Hapus Hari</button>
            </div>
            <input type="text" name="itineraries[${index}][title]" required placeholder="Judul aktivitas hari ke-${dayNumber}" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-teal-600">
            <textarea name="itineraries[${index}][description]" rows="2" placeholder="Rincian jam, lokasi, dan kegiatan..." class="w-full bg-white border border-slate-300 rounded-lg p-2.5 text-xs focus:outline-none focus:border-teal-600"></textarea>
        `;
        container.appendChild(div);
        reindexItineraries();
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateDurationDisplay();
    });
</script>
@endpush
@endsection
