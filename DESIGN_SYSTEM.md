# Design System — D'Java Tour & Travel

Dokumen ini adalah acuan visual dan teknis untuk semua UI customer-facing.
Tujuannya: halaman baru terasa satu keluarga dengan halaman lama, tanpa harus menyalin markup.

> **Pratinjau langsung:** `/design-system` (hanya tersedia saat `APP_ENV=local` atau `testing`).
> **Komponen:** `resources/views/components/ui/*` dan `resources/views/components/catalog/*`.

---

## 1. Prinsip

1. **Reuse sebelum membuat baru.** Cek `x-ui.*` dan `x-catalog.*` dulu.
2. **Satu primary action per area.** CTA utama berwarna teal; WhatsApp hanya untuk kanal percakapan.
3. **Konsisten bukan identik.** Ikuti token warna, tipografi, radius, spacing, dan pola layout — bukan copy-paste seluruh halaman.
4. **Aksesibel.** Label form eksplisit, `alt` pada gambar, focus ring terlihat, kontras teks memadai.
5. **Mobile-first.** Grid 1 kolom di mobile, 2–3 kolom di layar lebih besar.

---

## 2. Token

### 2.1 Warna

| Peran | Tailwind | Contoh pemakaian |
|---|---|---|
| Brand utama | `teal-700` | Tombol primary, harga, link aktif |
| Brand hover | `teal-800` | Hover tombol primary |
| Brand lembut | `teal-50`, `teal-100` | Badge brand, highlight, border |
| Latar halaman | `slate-50` / `indigo-50/50` | Body halaman detail / katalog |
| Panel section | `blue-50` | Panel spesifikasi & fasilitas di halaman detail |
| Teks utama | `slate-900` | Heading |
| Teks body | `slate-600` | Paragraf |
| Teks muted | `slate-500`, `slate-400` | Caption, label kecil |
| Border | `slate-200`, `slate-100` | Card, divider |
| Info | `blue-100` / `blue-900` | Chip metadata |
| Sukses | `emerald-50` / `emerald-800` | Termasuk, berhasil |
| Peringatan | `amber-100` / `amber-900` | Sisa kuota, catatan |
| Bahaya | `rose-50` / `rose-800` | Tidak termasuk, error |
| WhatsApp | `green-500` / `green-600` | CTA WhatsApp |

Jangan menambah warna brand baru. Varian gelap/terang memakai skala Tailwind yang sama.

### 2.2 Tipografi

Font: **DM Sans** (Google Fonts, sudah dimuat di layout halaman).

| Peran | Class | Catatan |
|---|---|---|
| Display / H1 | `text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight` | Maksimal 2 baris di desktop |
| Section / H2 | `text-xl sm:text-2xl font-extrabold tracking-tight` | Dipakai `x-ui.section-heading` |
| Card title | `text-base/lg font-extrabold` | Nama unit/paket |
| Body | `text-sm/base leading-7 text-slate-600` | Line-height lega |
| Caption | `text-xs text-slate-500` | Keterangan harga, syarat |
| Eyebrow | `text-xs font-extrabold uppercase tracking-wider text-teal-700` | Di atas H1/H2 |
| Harga | `text-lg/text-3xl font-black text-teal-700` | Angka lebih tebal dari heading |

### 2.3 Radius, shadow, spacing

| Elemen | Nilai |
|---|---|
| Button, input, select | `rounded-xl` / `rounded-2xl` |
| Inner panel | `rounded-2xl` |
| Card & section panel | `rounded-3xl` |
| Badge | `rounded-full` |
| Card default | `border border-slate-200 bg-white shadow-sm` |
| Card hover | `transition duration-300 hover:-translate-y-1 hover:shadow-lg` |
| Panel tarif | `shadow-lg` |
| Jarak antar section | `space-y-6` |
| Jarak grid card | `gap-4` / `gap-6` |
| Padding halaman | `py-6 md:py-8` (detail), `py-8 md:py-12` (katalog) |

### 2.4 Page shell

| Tipe halaman | Body | Container |
|---|---|---|
| Katalog & detail customer | `bg-indigo-50/50` / `bg-slate-50` | `<x-ui.page-container>` → `max-w-screen-2xl px-4 sm:px-6 lg:px-8` |
| Beranda (marketing) | section full-width bergantian | `container mx-auto px-4` per section |
| Auth/admin | mengikuti layout admin | di luar cakupan design system ini |

Halaman detail memakai grid `xl:grid-cols-12`: konten `xl:col-span-8`, panel tarif `xl:col-span-4 xl:sticky xl:top-24`.

---

## 3. Komponen

### 3.1 Button — `<x-ui.button>`

```blade
<x-ui.button href="{{ route('armada') }}" variant="primary" size="md">
    Lihat Armada
</x-ui.button>

<x-ui.button type="submit" size="lg" class="w-full">Cari Armada</x-ui.button>
```

| Prop | Nilai | Default |
|---|---|---|
| `href` | URL — jika diisi render `<a>`, jika kosong render `<button>` | `null` |
| `variant` | `primary`, `secondary`, `outline`, `ghost`, `ghost-danger`, `whatsapp`, `danger` | `primary` |
| `size` | `sm`, `md`, `lg` | `md` |
| `type` | `button`, `submit`, `reset` | `button` |

Aturan: satu `primary` per area. Aksi sekunder memakai `outline`/`ghost`. Jangan memakai `danger` untuk aksi non-destruktif.

### 3.2 Badge — `<x-ui.badge>`

```blade
<x-ui.badge variant="solid">{{ $vehicle->badge }}</x-ui.badge>
<x-ui.badge variant="info">7 kursi</x-ui.badge>
<x-ui.badge variant="success">Opsi supir tersedia</x-ui.badge>
```

Varian: `brand`, `solid`, `neutral`, `info`, `warning`, `success`, `danger`, `dark`.
Ukuran: `xs`, `sm`, `md`.

### 3.3 Card — `<x-ui.card>`

```blade
<x-ui.card padding="lg">...</x-ui.card>
<x-ui.card tone="muted" padding="lg">...</x-ui.card>
<x-ui.card as="article" padding="none" interactive class="overflow-hidden">...</x-ui.card>
```

| Prop | Nilai | Default |
|---|---|---|
| `as` | tag HTML (`div`, `article`, `section`) | `div` |
| `tone` | `white`, `muted`, `subtle`, `transparent` | `white` |
| `padding` | `none`, `sm`, `md`, `lg` | `none` |
| `interactive` | `true` → efek hover angkat | `false` |

Gunakan `tone="muted"` untuk panel section di dalam halaman detail (spesifikasi, fasilitas).
Gunakan `interactive` hanya jika seluruh card atau isinya bisa diklik.

### 3.4 Section heading — `<x-ui.section-heading>`

```blade
<x-ui.section-heading
    eyebrow="Garasi"
    title="Spesifikasi Unit"
    description="Kapasitas dan kenyamanan unit."
>
    <x-ui.button href="..." variant="ghost" size="sm">Lihat semua</x-ui.button>
</x-ui.section-heading>
```

Slot opsional di kanan untuk link/aksi. `level` menentukan tag heading (`2` default).

### 3.5 Breadcrumb — `<x-ui.breadcrumb>`

```blade
<x-ui.breadcrumb :items="[
    ['label' => 'Kembali ke Armada', 'url' => route('armada')],
    ['label' => 'Armada', 'url' => route('armada')],
    ['label' => $vehicle->name],
]" />
```

Item pertama dirender sebagai link kembali dengan panah. Item terakhir tanpa `url` menjadi halaman aktif.

### 3.6 Alert — `<x-ui.alert>`

```blade
<x-ui.alert variant="warning" title="Perhatian">
    Sisa kuota tinggal 3 kursi.
</x-ui.alert>
```

Varian: `info`, `success`, `warning`, `danger`. `dismissible` default `true`; set `false` untuk pesan permanen.

### 3.7 Empty state — `<x-ui.empty-state>`

```blade
<x-ui.empty-state
    title="Armada Tidak Ditemukan"
    description="Tidak ada armada yang cocok dengan filter."
    class="col-span-full"
>
    <x-ui.button href="{{ route('armada') }}">Reset Filter</x-ui.button>
</x-ui.empty-state>
```

### 3.8 Stat tile — `<x-ui.stat-tile>`

```blade
<x-ui.stat-tile label="Kapasitas" value="7 kursi" />
<x-ui.stat-tile label="Tarif" value="Rp 650.000" hint="per hari" />
```

Dipakai untuk spesifikasi armada dan ringkasan kecil. Selalu di dalam grid.

### 3.9 Form katalog — `<x-catalog.*>`

- `x-catalog.filter-card` — wrapper card + `<form method="GET">`; prop `action`.
- `x-catalog.text-field` — `name`, `label`, `value`, `placeholder`, `span`.
- `x-catalog.select-field` — `name`, `label`, `options`, `selected`, `placeholder`, `span`, `autoSubmit`.
- `x-catalog.result-toolbar` — jumlah hasil + select urutan; prop `action`, `sort`, `options`.

Semua halaman katalog wajib memakai komponen ini agar perilaku filter dan query string konsisten.

### 3.10 Page container — `<x-ui.page-container>`

```blade
<x-ui.page-container>...</x-ui.page-container>
<x-ui.page-container width="content">...</x-ui.page-container>
```

`width`: `wide` (default, `max-w-screen-2xl`), `content` (`max-w-5xl`), `narrow` (`max-w-3xl`).

---

## 4. Pola halaman

### 4.1 Katalog

1. Header: eyebrow, H1, deskripsi singkat.
2. Filter card: kontrol filter yang relevan (pencarian/select) + tombol primary.
3. Result toolbar: jumlah hasil + urutan.
4. Grid card `1 / 2 / 3` kolom.
5. Pagination (`$items->links()`).
6. Empty state di dalam grid dengan `col-span-full`.

### 4.2 Detail

1. Breadcrumb.
2. Header: badge, H1, deskripsi, chip metadata.
3. Grid 8/4:
   - Kiri: foto, panel spesifikasi (muted), termasuk/tidak termasuk (muted), item terkait.
   - Kanan: panel booking sticky berisi tarif unit, info DP, opsi sewa, **Tanggal Sewa** (Flatpickr), stepper **Jumlah Penumpang**, ringkasan biaya, CTA, dan kartu bantuan.
4. Flash message memakai `x-ui.alert`.
5. Panel booking memakai lebar `min-w-0` dan semua baris ringkasan memakai `justify-between` + nilai `shrink-0 whitespace-nowrap` agar teks tidak menabrak pinggir card.

### 4.3 CTA & konversi

- Paket wisata: CTA utama menuju formulir booking.
- Armada: CTA utama **Lanjut ke Formulir Booking** menuju route `armada.booking.form` dengan query `rental_date`, `pax`, `rental_option`, dan `duration=1`. Form armada mengikuti pola formulir paket; penyimpanan booking masih berupa pratinjau.
- Jangan menampilkan dua CTA dengan bobot visual sama.

### 4.4 Pencarian beranda

1. Tab mode memakai radio `mode=paket|mobil`; urutan tampilan **Paket Wisata** lalu **Sewa Mobil Privat**, default `paket`.
2. Form mengirim `GET` dengan `search=1` dan anchor `#hasil-pencarian`.
3. Ringkasan hasil menampilkan mode, kata kunci, rute, tanggal, kapasitas, jumlah hasil, dan tombol Reset.
4. Mode paket: filter kata kunci nama/kategori/badge/deskripsi, destinasi (ID paket), tanggal trip + sisa kursi, dan kuota trip bila tanggal kosong.
5. Mode mobil: filter kata kunci nama/kategori/deskripsi dan `seats >= passengers`; destinasi dan tanggal menjadi konteks pencarian/booking.
6. Opsi dropdown destinasi selalu berasal dari data penuh (`$destinationOptions`), bukan hasil filter, agar isi dropdown tidak berubah saat pencarian.

### 4.5 Formulir booking armada

1. Route: `/armada/{vehicle:slug}/formulir` (`armada.booking.form`).
2. Query yang dipertahankan dari panel detail: `rental_date`, `pax`, `rental_option`, `duration`.
3. Struktur mengikuti formulir paket: header, ringkasan biaya sticky, jadwal/penjemputan, data pemesan, upload identitas, metode DP, dan CTA.
4. Total sewa = tarif opsi per hari × durasi. Jumlah pax hanya untuk kapasitas, bukan pengali harga.
5. Tanggal memakai Flatpickr dengan tampilan `j F Y`; nilai asli tetap `Y-m-d`.
6. Tahap ini adalah pratinjau UI: tombol submit menampilkan notice dan tidak mengirim/menyimpan data.

---

## 5. Konvensi data armada

- `price_per_day` — harga tampilan lama / fallback.
- `price_with_driver` — tarif opsi dengan supir.
- `price_without_driver` — tarif opsi lepas kunci.
- `Vehicle::rentalOptions()` — mengembalikan opsi yang tersedia (fallback ke `price_per_day` bila kolom opsi kosong).
- `Vehicle::lowest_price` / `formatted_lowest_price` — harga terendah untuk label **"Mulai dari"** di katalog.
- `Vehicle::category_label` — label kategori dari konstanta `Vehicle::CATEGORIES`.

Jika menambah kategori, update `Vehicle::CATEGORIES`, validasi filter di `HomeController::armada()`, dan opsi di halaman katalog.

---

## 6. Hasil review halaman yang ada

| Halaman | Status | Catatan / tindak lanjut |
|---|---|---|
| Katalog armada `/armada` | **Sesuai** | Card, badge, button, empty state, dan shell sudah memakai komponen bersama. Tombol card = `Lihat Detail`. |
| Katalog paket `/paket-wisata` | **Sesuai** | Komponen filter/card/button/empty state sudah disamakan; lebar halaman mengikuti `x-ui.page-container`. |
| Detail armada `/armada/{slug}` | **Sesuai (baru)** | Mengikuti pola detail paket dengan konten kendaraan dan pemilih opsi supir. CTA utama menuju formulir booking armada. |
| Formulir booking armada `/armada/{slug}/formulir` | **Pratinjau** | Mengikuti layout formulir paket; tanggal, durasi, pax, dan opsi sewa dapat diubah. Submit masih menampilkan notice dan belum menyimpan data. |
| Detail paket `/paket-wisata/{slug}` | **Sebagian** | Layout, spacing, palet, flash message, breadcrumb, dan badge sudah sejalan. Chip metadata masih `rounded-lg` dan panel booking punya gaya sendiri karena logika kuota. Aman dirapikan bertahap tanpa mengubah alur. |
| Beranda `/` | **Sebagian** | Warna, tipografi, dan card armada sudah sejalan. Section pemasaran memakai container sendiri dan form pencarian kustom; sengaja dibiarkan agar tidak mengganggu layout marketing. |
| Navbar & footer | **Sesuai** | Menu desktop aktif mulai `lg` (1024px); tablet memakai menu hamburger. Overflow horizontal pada viewport 800–1023px sudah diperbaiki. |
| Area admin | **Di luar cakupan** | Design system ini fokus pada halaman customer. |

### Rekomendasi lanjutan (opsional)

1. Ganti chip di detail paket dengan `x-ui.badge` saat ada waktu untuk regresi visual.
2. Pecah panel booking detail paket menjadi komponen `x-booking.panel` jika logika kuota stabil.
3. Tambahkan komponen `x-ui.modal` jika muncul kebutuhan modal baru.

---

## 7. Checklist sebelum merge

- [ ] Memakai `x-ui.page-container` untuk halaman customer baru.
- [ ] Tombol memakai `x-ui.button`, badge memakai `x-ui.badge`, card memakai `x-ui.card`.
- [ ] Tidak ada warna brand baru di luar palet.
- [ ] Grid responsif di 360px, 768px, dan 1280px.
- [ ] Semua gambar punya `alt`; input punya `<label>`.
- [ ] Focus state terlihat (gunakan `focus-visible:ring` pada tombol).
- [ ] Empty state dan error state tersedia.
- [ ] Teks harga memakai format `Rp 1.234.000`.
- [ ] CTA utama jelas dan hanya satu per area.
- [ ] `npm run build` dan `php artisan view:cache` lolos.

---

## 8. Peta file

```
resources/views/components/ui/
├── alert.blade.php
├── badge.blade.php
├── breadcrumb.blade.php
├── button.blade.php
├── card.blade.php
├── empty-state.blade.php
├── page-container.blade.php
├── section-heading.blade.php
└── stat-tile.blade.php

resources/views/components/catalog/
├── filter-card.blade.php
├── result-toolbar.blade.php
├── select-field.blade.php
└── text-field.blade.php

resources/views/pages/design-system/index.blade.php   ← pratinjau /design-system
resources/views/pages/dashboard/armada/detail.blade.php
resources/views/pages/dashboard/armada/formulir/index.blade.php
resources/js/armada-detail.js                          ← Flatpickr + logika panel booking armada
resources/js/armada-booking-form.js                    ← interaksi formulir booking armada
resources/js/flatpickr-id.js                           ← locale Flatpickr Indonesia bersama
```

---

## 9. Catatan untuk agent berikutnya

- Baca dokumen ini sebelum mengubah Blade UI.
- Jangan menganggap "ikuti UI halaman X" berarti menyalin markup. Ambil pola dan komponennya.
- Jika menemukan pola baru yang dipakai lebih dari satu halaman, jadikan komponen `x-ui.*` dan tambahkan contohnya di `/design-system`.
- Jika menambah komponen atau varian, update dokumen ini dan halaman pratinjau.
