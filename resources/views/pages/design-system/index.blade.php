<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Design System - D'Java Tour &amp; Travel</title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'DM Sans', Arial, sans-serif;
        }
    </style>
</head>

<body class="flex min-h-screen flex-col bg-slate-50 text-slate-800 antialiased">

    @include('components.navbar.navbar')

    <main class="flex-1 py-8 md:py-12">
        <x-ui.page-container>

            {{-- HEADER --}}
            <header class="mb-8 max-w-4xl">
                <x-ui.badge variant="brand" size="md" class="mb-3 uppercase tracking-wide">
                    Internal · Local Only
                </x-ui.badge>

                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 md:text-5xl">
                    Design System
                </h1>

                <p class="mt-3 text-base leading-7 text-slate-600">
                    Acuan visual D'Java Tour &amp; Travel. Halaman ini adalah pratinjau komponen,
                    token warna, dan pola halaman yang dipakai di seluruh situs.
                    Baca juga <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">DESIGN_SYSTEM.md</code>
                    sebelum membuat UI baru.
                </p>
            </header>

            <x-ui.alert variant="info" title="Cara memakai design system" :dismissible="false" class="mb-8">
                Ikuti token, komponen, dan pola layout-nya. Jangan menyalin markup halaman lain mentah-mentah —
                gunakan komponen <code class="rounded bg-white/70 px-1.5 py-0.5 text-xs">&lt;x-ui.*&gt;</code>
                agar perubahan style cukup dilakukan di satu tempat.
            </x-ui.alert>

            {{-- FOUNDATIONS --}}
            <section class="mb-10">
                <x-ui.section-heading
                    eyebrow="Foundations"
                    title="Warna"
                    description="Palet utama: teal sebagai brand, indigo sebagai latar hangat, slate untuk teks."
                />

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ([
                        ['Brand 700', 'bg-teal-700', 'Primary action'],
                        ['Brand 600', 'bg-teal-600', 'Badge solid'],
                        ['Brand 50', 'bg-teal-50', 'Highlight'],
                        ['Indigo 50', 'bg-indigo-50', 'Page tint'],
                        ['Ink 900', 'bg-slate-900', 'Heading'],
                        ['Ink 500', 'bg-slate-500', 'Body muted'],
                    ] as [$name, $class, $usage])
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            <div class="h-16 {{ $class }}"></div>
                            <div class="p-3">
                                <p class="m-0 text-xs font-extrabold text-slate-900">{{ $name }}</p>
                                <p class="m-0 mt-0.5 text-[11px] text-slate-500">{{ $usage }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ([
                        ['Semantic info', 'bg-blue-100 text-blue-900'],
                        ['Semantic success', 'bg-emerald-50 text-emerald-800'],
                        ['Semantic warning', 'bg-amber-100 text-amber-900'],
                        ['Semantic danger', 'bg-rose-50 text-rose-800'],
                    ] as [$name, $class])
                        <div class="rounded-xl px-3 py-2 text-xs font-bold {{ $class }}">
                            {{ $name }}
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="mb-10">
                <x-ui.section-heading
                    eyebrow="Foundations"
                    title="Tipografi"
                    description="DM Sans. Heading tebal dan rapat, body ringan dengan line-height lega."
                />

                <x-ui.card tone="subtle" padding="lg">
                    <div class="space-y-4">
                        <div>
                            <span class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">Display / H1</span>
                            <p class="m-0 text-3xl font-extrabold leading-tight tracking-tight text-slate-900 md:text-5xl">
                                Jelajahi Jawa Timur
                            </p>
                        </div>

                        <div>
                            <span class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">Section / H2</span>
                            <p class="m-0 text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">
                                Armada Pilihan
                            </p>
                        </div>

                        <div>
                            <span class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">Eyebrow</span>
                            <p class="m-0 text-xs font-extrabold uppercase tracking-wider text-teal-700">
                                Garasi &amp; Layanan Transportasi
                            </p>
                        </div>

                        <div>
                            <span class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">Body</span>
                            <p class="m-0 max-w-2xl text-sm leading-relaxed text-slate-600">
                                Kendaraan terawat dengan standar servis berkala, driver ramah, dan
                                proteksi perjalanan lengkap untuk rute Jawa Timur.
                            </p>
                        </div>

                        <div>
                            <span class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-slate-400">Caption</span>
                            <p class="m-0 text-xs text-slate-500">Harga dapat berubah sesuai rute dan ketersediaan unit.</p>
                        </div>
                    </div>
                </x-ui.card>
            </section>

            {{-- COMPONENTS --}}
            <section class="mb-10">
                <x-ui.section-heading
                    eyebrow="Components"
                    title="Button"
                    description="Satu primary action per area. WhatsApp hanya untuk kanal percakapan."
                />

                <x-ui.card padding="lg">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-ui.button>Primary</x-ui.button>
                        <x-ui.button variant="secondary">Secondary</x-ui.button>
                        <x-ui.button variant="outline">Outline</x-ui.button>
                        <x-ui.button variant="ghost">Ghost</x-ui.button>
                        <x-ui.button variant="ghost-danger">Reset</x-ui.button>
                        <x-ui.button variant="whatsapp">WhatsApp</x-ui.button>
                        <x-ui.button variant="danger">Danger</x-ui.button>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <x-ui.button size="sm">Small</x-ui.button>
                        <x-ui.button size="md">Medium</x-ui.button>
                        <x-ui.button size="lg">Large</x-ui.button>
                        <x-ui.button variant="outline" disabled>Disabled</x-ui.button>
                    </div>

                    <div class="mt-5 rounded-2xl bg-slate-900 p-4">
                        <pre class="m-0 overflow-x-auto text-xs leading-relaxed text-slate-200"><code>@verbatim<x-ui.button href="{{ route('armada') }}" variant="primary" size="md">
    Lihat Armada
</x-ui.button>@endverbatim</code></pre>
                    </div>
                </x-ui.card>
            </section>

            <section class="mb-10">
                <x-ui.section-heading
                    eyebrow="Components"
                    title="Badge & Chip"
                    description="Badge selalu pill. Gunakan untuk status, kategori, dan metadata singkat."
                />

                <x-ui.card padding="lg">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.badge>Neutral</x-ui.badge>
                        <x-ui.badge variant="brand">Brand</x-ui.badge>
                        <x-ui.badge variant="solid">Solid</x-ui.badge>
                        <x-ui.badge variant="info">Info</x-ui.badge>
                        <x-ui.badge variant="success">Success</x-ui.badge>
                        <x-ui.badge variant="warning">Warning</x-ui.badge>
                        <x-ui.badge variant="danger">Danger</x-ui.badge>
                        <x-ui.badge variant="dark">Dark</x-ui.badge>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <x-ui.badge size="md" variant="info"><span aria-hidden="true">◷</span> 7 kursi</x-ui.badge>
                        <x-ui.badge size="md" variant="info"><span aria-hidden="true">⌖</span> MPV Keluarga</x-ui.badge>
                        <x-ui.badge size="md" variant="warning">4 koper</x-ui.badge>
                        <x-ui.badge size="md" variant="success">Opsi supir tersedia</x-ui.badge>
                    </div>
                </x-ui.card>
            </section>

            <section class="mb-10">
                <x-ui.section-heading
                    eyebrow="Components"
                    title="Card"
                    description="Card putih untuk konten utama, muted untuk panel section di dalam halaman detail."
                />

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <x-ui.card padding="md">
                        <x-ui.badge variant="brand" class="mb-3">White</x-ui.badge>
                        <h3 class="m-0 text-base font-extrabold text-slate-900">Konten utama</h3>
                        <p class="mb-0 mt-1 text-sm leading-relaxed text-slate-600">
                            Dipakai untuk kartu katalog, panel tarif, dan kartu informasi.
                        </p>
                    </x-ui.card>

                    <x-ui.card tone="muted" padding="md">
                        <x-ui.badge variant="info" class="mb-3">Muted</x-ui.badge>
                        <h3 class="m-0 text-base font-extrabold text-slate-900">Panel section</h3>
                        <p class="mb-0 mt-1 text-sm leading-relaxed text-slate-600">
                            Latar biru lembut untuk mengelompokkan konten di halaman detail.
                        </p>
                    </x-ui.card>

                    <x-ui.card tone="subtle" padding="md" interactive>
                        <x-ui.badge variant="neutral" class="mb-3">Interactive</x-ui.badge>
                        <h3 class="m-0 text-base font-extrabold text-slate-900">Kartu interaktif</h3>
                        <p class="mb-0 mt-1 text-sm leading-relaxed text-slate-600">
                            Efek angkat halus saat hover, untuk item yang bisa diklik.
                        </p>
                    </x-ui.card>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <x-ui.stat-tile label="Kapasitas" value="7 kursi" />
                    <x-ui.stat-tile label="Bagasi" value="4 koper" />
                    <x-ui.stat-tile label="Transmisi" value="Automatic" />
                    <x-ui.stat-tile label="Tarif" value="Rp 650.000" hint="per hari" />
                </div>
            </section>

            <section class="mb-10">
                <x-ui.section-heading
                    eyebrow="Components"
                    title="Form & Filter"
                    description="Input dan select memakai komponen katalog agar semua filter konsisten."
                />

                <x-catalog.filter-card action="#">
                    <x-catalog.text-field
                        name="demo_q"
                        label="Cari armada"
                        placeholder="Cari nama unit atau tipe..."
                    />

                    <x-catalog.select-field
                        name="demo_kategori"
                        label="Kategori"
                        :options="['mpv' => 'MPV Keluarga', 'premium' => 'Premium & Hybrid', 'minibus' => 'Minibus Rombongan']"
                        placeholder="Semua Kategori"
                        span="xl:col-span-3"
                        :autoSubmit="false"
                    />

                    <x-catalog.select-field
                        name="demo_urutkan"
                        label="Urutkan"
                        :options="['rekomendasi' => 'Rekomendasi', 'harga_terendah' => 'Harga Terendah']"
                        span="xl:col-span-3"
                        :autoSubmit="false"
                    />

                    <div class="xl:col-span-2">
                        <x-ui.button type="submit" size="lg" class="w-full">
                            Terapkan
                        </x-ui.button>
                    </div>
                </x-catalog.filter-card>
            </section>

            <section class="mb-10">
                <x-ui.section-heading
                    eyebrow="Components"
                    title="Breadcrumb, Alert & Empty State"
                    description="Pola navigasi dan umpan balik yang dipakai di halaman detail dan katalog."
                />

                <x-ui.breadcrumb :items="[
                    ['label' => 'Kembali ke Armada', 'url' => route('armada')],
                    ['label' => 'Armada', 'url' => route('armada')],
                    ['label' => 'Toyota Innova Reborn'],
                ]" />

                <div class="space-y-3">
                    <x-ui.alert variant="info" title="Informasi" :dismissible="false">
                        Kuota jadwal diperbarui secara berkala. Konfirmasi akhir dilakukan oleh tim kami.
                    </x-ui.alert>

                    <x-ui.alert variant="success" title="Berhasil" :dismissible="false">
                        Permintaan reservasi berhasil dicatat.
                    </x-ui.alert>

                    <x-ui.alert variant="warning" title="Perhatian">
                        Sisa kuota tinggal 3 kursi untuk jadwal ini.
                    </x-ui.alert>

                    <x-ui.alert variant="danger" title="Gagal">
                        Jadwal yang dipilih sudah penuh. Silakan pilih jadwal lain.
                    </x-ui.alert>
                </div>

                <div class="mt-4">
                    <x-ui.empty-state
                        title="Armada Tidak Ditemukan"
                        description="Tidak ada armada yang cocok dengan filter Anda. Coba kata kunci lain atau reset filter."
                    >
                        <x-ui.button size="md">Reset Filter</x-ui.button>
                    </x-ui.empty-state>
                </div>
            </section>

            <section class="mb-10">
                <x-ui.section-heading
                    eyebrow="Patterns"
                    title="Pola Halaman"
                    description="Dua pola utama yang harus diikuti saat membuat halaman baru."
                />

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <x-ui.card tone="muted" padding="lg">
                        <x-ui.badge variant="info" class="mb-3">Katalog</x-ui.badge>
                        <h3 class="m-0 text-lg font-extrabold text-slate-900">Header → Filter → Toolbar → Grid</h3>
                        <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm text-slate-600">
                            <li>Header dengan eyebrow, H1, deskripsi singkat.</li>
                            <li>Filter card: kontrol filter yang relevan (pencarian/select) + tombol utama.</li>
                            <li>Result toolbar: jumlah hasil + urutan.</li>
                            <li>Grid 1/2/3 kolom, lalu pagination.</li>
                        </ol>
                    </x-ui.card>

                    <x-ui.card tone="muted" padding="lg">
                        <x-ui.badge variant="info" class="mb-3">Detail</x-ui.badge>
                        <h3 class="m-0 text-lg font-extrabold text-slate-900">Breadcrumb → Header → 8/4 Grid</h3>
                        <ol class="mt-3 list-decimal space-y-1.5 pl-5 text-sm text-slate-600">
                            <li>Breadcrumb dengan link kembali.</li>
                            <li>Header: badge, H1, deskripsi, chip metadata.</li>
                            <li>Kolom kiri 8: foto, spesifikasi, fasilitas, terkait.</li>
                            <li>Kolom kanan 4: panel tarif/CTA sticky.</li>
                        </ol>
                    </x-ui.card>
                </div>
            </section>

            <section class="mb-10">
                <x-ui.section-heading
                    eyebrow="Governance"
                    title="Status Kesesuaian Halaman"
                    description="Hasil review singkat terhadap halaman yang sudah ada."
                />

                <x-ui.card padding="none" class="overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] border-collapse text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 font-bold">Halaman</th>
                                    <th class="px-4 py-3 font-bold">Status</th>
                                    <th class="px-4 py-3 font-bold">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800">Katalog armada</td>
                                    <td class="px-4 py-3"><x-ui.badge variant="success">Sesuai</x-ui.badge></td>
                                    <td class="px-4 py-3 text-slate-600">Memakai filter, card, badge, button, dan empty state bersama.</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800">Katalog paket wisata</td>
                                    <td class="px-4 py-3"><x-ui.badge variant="success">Sesuai</x-ui.badge></td>
                                    <td class="px-4 py-3 text-slate-600">Sudah disamakan dengan komponen UI dan lebar halaman.</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800">Detail paket wisata</td>
                                    <td class="px-4 py-3"><x-ui.badge variant="warning">Sebagian</x-ui.badge></td>
                                    <td class="px-4 py-3 text-slate-600">Flash message, breadcrumb, dan badge sudah memakai komponen bersama. Chip metadata dan panel booking masih kustom karena logika kuota.</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800">Navbar &amp; footer</td>
                                    <td class="px-4 py-3"><x-ui.badge variant="success">Sesuai</x-ui.badge></td>
                                    <td class="px-4 py-3 text-slate-600">Menu desktop aktif mulai <code class="rounded bg-slate-100 px-1 py-0.5 text-xs">lg</code>; tablet memakai hamburger. Overflow 800–1023px sudah diperbaiki.</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800">Beranda</td>
                                    <td class="px-4 py-3"><x-ui.badge variant="warning">Sebagian</x-ui.badge></td>
                                    <td class="px-4 py-3 text-slate-600">Section pemasaran memakai container sendiri; warna dan tipografi sudah sejalan.</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-slate-800">Area admin</td>
                                    <td class="px-4 py-3"><x-ui.badge variant="neutral">Di luar cakupan</x-ui.badge></td>
                                    <td class="px-4 py-3 text-slate-600">Fokus design system ini adalah halaman customer.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </section>

            <section>
                <x-ui.section-heading
                    eyebrow="Governance"
                    title="Aturan Singkat"
                    description="Checklist sebelum menambah UI baru."
                />

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <x-ui.card tone="muted" padding="md">
                        <h3 class="m-0 text-sm font-extrabold text-emerald-800">Lakukan</h3>
                        <ul class="mt-2 list-disc space-y-1.5 pl-5 text-sm text-slate-600">
                            <li>Pakai komponen <code class="rounded bg-white px-1 py-0.5 text-xs">&lt;x-ui.*&gt;</code> lebih dulu.</li>
                            <li>Pertahankan satu primary CTA per area.</li>
                            <li>Gunakan <code class="rounded bg-white px-1 py-0.5 text-xs">rounded-3xl</code> untuk card, <code class="rounded bg-white px-1 py-0.5 text-xs">rounded-xl</code> untuk button.</li>
                            <li>Selalu beri label, alt text, dan focus ring.</li>
                        </ul>
                    </x-ui.card>

                    <x-ui.card tone="muted" padding="md">
                        <h3 class="m-0 text-sm font-extrabold text-rose-800">Hindari</h3>
                        <ul class="mt-2 list-disc space-y-1.5 pl-5 text-sm text-slate-600">
                            <li>Menyalin seluruh markup halaman lain tanpa alasan.</li>
                            <li>Menambah warna brand baru di luar palet.</li>
                            <li>Menumpuk dua tombol utama dengan bobot sama.</li>
                            <li>Memakai emoji sebagai satu-satunya ikon fungsional.</li>
                        </ul>
                    </x-ui.card>
                </div>
            </section>

        </x-ui.page-container>
    </main>

    @include('components.footer.footer')

</body>

</html>
