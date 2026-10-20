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
    <form action="{{ route('admin.packages.update', $package->id) }}" method="POST" class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs space-y-6">
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
                        <option value="Wisata Bahari & Island Hopping" {{ $package->category === 'Wisata Bahari & Island Hopping' ? 'selected' : '' }}>Wisata Bahari &amp; Island Hopping</option>
                        <option value="Mountain Trekking & Sunrise" {{ $package->category === 'Mountain Trekking & Sunrise' ? 'selected' : '' }}>Mountain Trekking &amp; Sunrise</option>
                        <option value="Cultural, Heritage & City Tour" {{ $package->category === 'Cultural, Heritage & City Tour' ? 'selected' : '' }}>Cultural, Heritage &amp; City Tour</option>
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
                    <label class="block text-xs font-bold text-slate-700 mb-1">Durasi Perjalanan</label>
                    <input type="text" name="duration" value="{{ old('duration', $package->duration) }}" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Harga per Orang (IDR)</label>
                    <input type="number" name="price" value="{{ old('price', $package->price) }}" required min="0" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Badge Promosi (Opsional)</label>
                    <input type="text" name="badge" value="{{ old('badge', $package->badge) }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">URL Foto Sampul</label>
                    <input type="url" name="image_url" value="{{ old('image_url', $package->image_url) }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:border-teal-600">
                </div>

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

        {{-- BUTTON ACTIONS --}}
        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
            <a href="{{ route('admin.packages.index') }}" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition">Batal</a>
            <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-teal-700 hover:bg-teal-800 rounded-xl shadow-sm transition">
                Perbarui Paket
            </button>
        </div>
    </form>

</div>
@endsection
