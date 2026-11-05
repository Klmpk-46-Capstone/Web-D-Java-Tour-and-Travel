@php
    $isEdit = isset($unit);
    $selectedVehicleId = (int) old('vehicle_id', $unit->vehicle_id ?? 0);
    $selectedStatus = old('status', $unit->status ?? \App\Models\VehicleUnit::STATUS_ACTIVE);
@endphp

<form
    action="{{ $isEdit ? route('admin.units.update', $unit) : route('admin.units.store') }}"
    method="POST"
    class="space-y-5"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div>
        <label for="vehicle_id" class="mb-1.5 block text-xs font-extrabold text-slate-700">Tipe Armada</label>
        <select id="vehicle_id" name="vehicle_id" required class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700 focus:border-teal-600 focus:bg-white focus:outline-none">
            <option value="">Pilih tipe armada</option>
            @foreach ($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" @selected($selectedVehicleId === $vehicle->id)>
                    {{ $vehicle->name }} &middot; {{ $vehicle->seats }} kursi
                </option>
            @endforeach
        </select>
        @error('vehicle_id')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="plate_number" class="mb-1.5 block text-xs font-extrabold text-slate-700">Plat Nomor</label>
        <input
            id="plate_number"
            type="text"
            name="plate_number"
            required
            maxlength="20"
            value="{{ old('plate_number', $unit->plate_number ?? '') }}"
            placeholder="Cth: B 1234 CKR"
            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 font-mono text-sm font-bold uppercase tracking-wider text-slate-800 focus:border-teal-600 focus:bg-white focus:outline-none"
        >
        <p class="mt-1 text-[11px] text-slate-400">Huruf akan otomatis diubah menjadi kapital. Plat harus unik.</p>
        @error('plate_number')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="mb-1.5 block text-xs font-extrabold text-slate-700">Status Unit</label>
        <select id="status" name="status" required class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700 focus:border-teal-600 focus:bg-white focus:outline-none">
            @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-[11px] text-slate-400">Unit berstatus Servis tidak dihitung sebagai armada tersedia.</p>
        @error('status')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="notes" class="mb-1.5 block text-xs font-extrabold text-slate-700">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
        <textarea id="notes" name="notes" rows="4" maxlength="1000" placeholder="Cth: Servis berkala bulan November." class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 focus:border-teal-600 focus:bg-white focus:outline-none">{{ old('notes', $unit->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Unit' }}
        </button>
        <a href="{{ route('admin.units.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 transition hover:border-teal-600/40 hover:text-teal-800">
            Batal
        </a>
    </div>
</form>
