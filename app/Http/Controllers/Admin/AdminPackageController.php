<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminPackageController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $routeType = $request->query('route_type');
        $search = $request->query('search');

        $packages = Package::with(['itineraries', 'trips'])
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($routeType, fn($q) => $q->where('route_type', $routeType))
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10);

        return view('admin.packages.index', compact('packages', 'category', 'routeType', 'search'));
    }

    public function featured(): View
    {
        $featuredPackages = Package::with(['itineraries', 'trips'])
            ->where('is_featured', true)
            ->latest()
            ->get();

        $availablePackages = Package::where('is_featured', false)->get();

        return view('admin.packages.featured', compact('featuredPackages', 'availablePackages'));
    }

    public function create(): View
    {
        return view('admin.packages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:100'],
            'route_type' => ['required', 'in:bahari,gunung,heritage'],
            'duration' => ['required', 'string', 'max:80'],
            'price' => ['required', 'integer', 'min:0'],
            'badge' => ['nullable', 'string', 'max:80'],
            'image_url' => ['nullable', 'url'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'itineraries' => ['nullable', 'array'],
            'itineraries.*.title' => ['required_with:itineraries', 'string'],
            'itineraries.*.description' => ['nullable', 'string'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);
        unset($validated['image']);

        $uploadedImageUrl = $this->storeUploadedImage($request);
        $validated['image_url'] = $uploadedImageUrl ?? ($validated['image_url'] ?? null);

        $package = Package::create($validated);

        if (!empty($validated['itineraries'])) {
            foreach ($validated['itineraries'] as $idx => $it) {
                Itinerary::create([
                    'package_id' => $package->id,
                    'day_number' => $idx + 1,
                    'title' => $it['title'],
                    'description' => $it['description'] ?? '',
                ]);
            }
        }

        return redirect()->route('admin.packages.index')
            ->with('success', "Paket wisata '{$package->name}' berhasil ditambahkan ke katalog.");
    }

    public function edit(Package $package): View
    {
        $package->load('itineraries');
        return view('admin.packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:100'],
            'route_type' => ['required', 'in:bahari,gunung,heritage'],
            'duration' => ['required', 'string', 'max:80'],
            'price' => ['required', 'integer', 'min:0'],
            'badge' => ['nullable', 'string', 'max:80'],
            'image_url' => ['nullable', 'url'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');
        unset($validated['image']);

        $uploadedImageUrl = $this->storeUploadedImage($request);
        $newImageUrl = $uploadedImageUrl ?? ($validated['image_url'] ?? null);

        if ($newImageUrl !== $package->image_url) {
            $this->deleteStoredImage($package->image_url);
        }

        $validated['image_url'] = $newImageUrl;

        $package->update($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', "Paket wisata '{$package->name}' berhasil diperbarui.");
    }

    public function destroy(Package $package): RedirectResponse
    {
        $name = $package->name;
        $imageUrl = $package->image_url;

        $package->delete();

        $this->deleteStoredImage($imageUrl);

        return redirect()->route('admin.packages.index')
            ->with('success', "Paket '{$name}' telah dihapus dari sistem.");
    }

    public function toggleFeatured(Request $request, Package $package): JsonResponse|RedirectResponse
    {
        $package->is_featured = !$package->is_featured;
        $package->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_featured' => $package->is_featured,
                'message' => $package->is_featured
                    ? "Paket '{$package->name}' kini berstatus Rekomendasi Utama (Featured) di beranda customer."
                    : "Paket '{$package->name}' dicopot dari Rekomendasi Utama.",
            ]);
        }

        return back()->with('success', "Status Rekomendasi Utama paket '{$package->name}' berhasil diubah.");
    }

    public function toggleStatus(Request $request, Package $package): JsonResponse|RedirectResponse
    {
        $package->is_active = !$package->is_active;
        $package->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $package->is_active,
                'message' => $package->is_active
                    ? "Paket '{$package->name}' telah diaktifkan."
                    : "Paket '{$package->name}' telah dinonaktifkan.",
            ]);
        }

        return back()->with('success', "Status aktif paket '{$package->name}' diperbarui.");
    }

    private function storeUploadedImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $path = $request->file('image')->store('packages', 'public');

        if (! is_string($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    private function deleteStoredImage(?string $imageUrl): void
    {
        if (blank($imageUrl)) {
            return;
        }

        $urlPath = parse_url($imageUrl, PHP_URL_PATH);

        if (is_string($urlPath) && Str::startsWith($urlPath, '/storage/')) {
            $imageHost = parse_url($imageUrl, PHP_URL_HOST);
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

            if ($imageHost && $appHost && $imageHost !== $appHost) {
                return;
            }

            $relativePath = ltrim(Str::after($urlPath, '/storage/'), '/');
        } elseif (! Str::startsWith($imageUrl, ['http://', 'https://'])) {
            $relativePath = ltrim($imageUrl, '/');
        } else {
            return;
        }

        if (
            $relativePath === ''
            || ! Str::startsWith($relativePath, 'packages/')
            || Str::contains($relativePath, '..')
        ) {
            return;
        }

        Storage::disk('public')->delete($relativePath);
    }
}
