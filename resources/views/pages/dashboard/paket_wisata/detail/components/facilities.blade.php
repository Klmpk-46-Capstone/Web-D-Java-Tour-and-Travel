{{-- Included and excluded --}}
<section class="rounded-2xl bg-blue-50 p-4 sm:p-6">
    <h2 class="mb-5 text-xl font-bold text-slate-900 sm:text-2xl">
        Fasilitas Termasuk &amp; Tidak Termasuk
    </h2>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="rounded-xl bg-white p-5">
            <h3 class="mb-3 font-bold text-teal-800">
                <span aria-hidden="true">✓</span> Termasuk
            </h3>

            <ul class="space-y-3 text-sm leading-6 text-slate-600">
                @foreach ($includes as $item)
                    <li class="flex gap-2">
                        <span class="font-bold text-teal-700" aria-hidden="true">✓</span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>

            <p class="mt-4 text-xs leading-5 text-slate-500">
                Rincian final mengikuti informasi resmi dari pengelola paket.
            </p>
        </div>

        <div class="rounded-xl bg-white p-5">
            <h3 class="mb-3 font-bold text-slate-700">
                <span aria-hidden="true">⊗</span> Tidak Termasuk
            </h3>

            <ul class="space-y-3 text-sm leading-6 text-slate-600">
                @foreach ($excludes as $item)
                    <li class="flex gap-2">
                        <span class="font-bold text-slate-500" aria-hidden="true">×</span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>

            <p class="mt-4 text-xs leading-5 text-slate-500">
                Periksa rincian paket sebelum menyelesaikan pemesanan.
            </p>
        </div>
    </div>
</section>
