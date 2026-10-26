<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminVehicleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $category = $request->query('category');
        $transmission = $request->query('transmission');

        $vehicles = Vehicle::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->when($search !== '', function ($query) use ($search) {
                $keyword = '%' . $search . '%';

                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('name', 'like', $keyword)
                        ->orWhere('badge', 'like', $keyword)
                        ->orWhere('category', 'like', $keyword)
                        ->orWhere('transmission', 'like', $keyword)
                        ->orWhere('description', 'like', $keyword);
                });
            })
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when(
                $transmission === 'at',
                fn ($query) => $query->where('transmission', 'like', '%matic%')
            )
            ->when(
                $transmission === 'mt',
                fn ($query) => $query->where('transmission', 'like', '%manual%')
            )
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $categories = Vehicle::CATEGORIES;

        return view('admin.armada.index', compact(
            'vehicles',
            'categories',
            'search',
            'category',
            'transmission'
        ));
    }

    public function create(): View
    {
        return view('admin.armada.create', [
            'categories' => Vehicle::CATEGORIES,
            'transmissionOptions' => $this->transmissionOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        unset($validated['image']);

        $baseSlug = Str::slug($validated['name']) ?: 'armada';
        $validated['slug'] = $baseSlug . '-' . Str::lower(Str::random(4));
        $validated['includes_driver'] = $request->boolean('includes_driver');
        $validated['is_active'] = $request->boolean('is_active');

        if ($imagePath = $this->storeUploadedImage($request)) {
            $validated['image_path'] = $imagePath;
        }

        $vehicle = Vehicle::create($validated);

        return redirect()
            ->route('admin.armada.index')
            ->with(
                'success',
                "Armada '{$vehicle->name}' berhasil ditambahkan ke katalog."
            );
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('admin.armada.edit', [
            'vehicle' => $vehicle,
            'categories' => Vehicle::CATEGORIES,
            'transmissionOptions' => $this->transmissionOptions($vehicle->transmission),
        ]);
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $this->validatedData($request);
        unset($validated['image']);

        $validated['includes_driver'] = $request->boolean('includes_driver');
        $validated['is_active'] = $request->boolean('is_active');

        if ($imagePath = $this->storeUploadedImage($request, $vehicle)) {
            $validated['image_path'] = $imagePath;
        }

        $vehicle->update($validated);

        return redirect()
            ->route('admin.armada.index')
            ->with(
                'success',
                "Armada '{$vehicle->name}' berhasil diperbarui."
            );
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        if ($vehicle->bookings()->exists()) {
            return redirect()
                ->route('admin.armada.index')
                ->with(
                    'error',
                    "Armada '{$vehicle->name}' tidak dapat dihapus karena sudah memiliki riwayat booking. Nonaktifkan armada ini sebagai gantinya."
                );
        }

        $name = $vehicle->name;

        $this->deleteStoredImage($vehicle);
        $vehicle->delete();

        return redirect()
            ->route('admin.armada.index')
            ->with('success', "Armada '{$name}' telah dihapus dari sistem.");
    }

    public function toggleStatus(Request $request, Vehicle $vehicle): JsonResponse|RedirectResponse
    {
        $vehicle->is_active = ! $vehicle->is_active;
        $vehicle->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $vehicle->is_active,
                'message' => $vehicle->is_active
                    ? "Armada '{$vehicle->name}' telah diaktifkan."
                    : "Armada '{$vehicle->name}' telah dinonaktifkan.",
            ]);
        }

        return back()->with(
            'success',
            "Status aktif armada '{$vehicle->name}' berhasil diperbarui."
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => [
                'required',
                'string',
                Rule::in(array_keys(Vehicle::CATEGORIES)),
            ],
            'badge' => ['nullable', 'string', 'max:80'],
            'seats' => ['required', 'integer', 'min:1', 'max:100'],
            'luggage_capacity' => ['required', 'integer', 'min:0', 'max:50'],
            'transmission' => ['nullable', 'string', 'max:50'],
            'price_per_day' => ['required', 'integer', 'min:0'],
            'price_with_driver' => ['nullable', 'integer', 'min:0'],
            'price_without_driver' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'includes_driver' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'image.image' => 'File foto armada harus berupa gambar yang valid.',
            'image.mimes' => 'Foto armada harus berformat JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto armada maksimal 2 MB.',
        ]);
    }

    private function storeUploadedImage(
        Request $request,
        ?Vehicle $vehicle = null
    ): ?string {
        if (! $request->hasFile('image')) {
            return null;
        }

        $path = $request->file('image')->store('vehicles', 'public');

        if (! is_string($path)) {
            return null;
        }

        if ($vehicle) {
            $this->deleteStoredImage($vehicle);
        }

        return $path;
    }

    private function deleteStoredImage(Vehicle $vehicle): void
    {
        $path = $vehicle->image_path;

        if (
            blank($path)
            || Str::startsWith($path, ['http://', 'https://'])
        ) {
            return;
        }

        Storage::disk('public')->delete(ltrim($path, '/'));
    }

    /**
     * Pilihan transmisi umum + nilai lama agar data existing tetap tampil.
     *
     * @return array<int, string>
     */
    private function transmissionOptions(?string $current = null): array
    {
        $options = [
            'Automatic',
            'Manual',
            'Matic / Manual',
        ];

        if (filled($current)) {
            $options[] = $current;
        }

        return array_values(array_unique($options));
    }
}
