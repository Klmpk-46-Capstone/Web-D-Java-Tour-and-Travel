<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status'); // all, pending, hidden, approved

        $reviews = Review::with(['package', 'user'])
            ->when($status === 'hidden', fn($q) => $q->where('is_hidden', true))
            ->when($status === 'approved', fn($q) => $q->where('is_approved', true)->where('is_hidden', false))
            ->when($status === 'pending', fn($q) => $q->where('is_approved', false))
            ->latest('id')
            ->paginate(12);

        $totalReviews = Review::count();
        $approvedCount = Review::where('is_approved', true)->where('is_hidden', false)->count();
        $hiddenCount = Review::where('is_hidden', true)->count();
        $pendingCount = Review::where('is_approved', false)->count();

        return view('admin.reviews.index', compact(
            'reviews',
            'status',
            'totalReviews',
            'approvedCount',
            'hiddenCount',
            'pendingCount'
        ));
    }

    public function toggleVisibility(Request $request, Review $review): JsonResponse|RedirectResponse
    {
        $review->is_hidden = !$review->is_hidden;
        $review->save();

        $this->syncPackageRating($review->package_id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_hidden' => $review->is_hidden,
                'message' => $review->is_hidden
                    ? "Ulasan dari {$review->customer_name} telah disembunyikan dari halaman publik."
                    : "Ulasan dari {$review->customer_name} kini kembali ditampilkan ke publik.",
            ]);
        }

        return redirect()->back()->with('success', "Status visibilitas ulasan berhasil diperbarui.");
    }

    public function approve(Request $request, Review $review): JsonResponse|RedirectResponse
    {
        $review->update([
            'is_approved' => true,
            'is_hidden' => false,
        ]);

        $this->syncPackageRating($review->package_id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Ulasan dari {$review->customer_name} telah disetujui untuk tayang publik.",
            ]);
        }

        return redirect()->back()->with('success', "Ulasan berhasil disetujui.");
    }

    public function destroy(Review $review): RedirectResponse
    {
        $packageId = $review->package_id;
        $review->delete();

        $this->syncPackageRating($packageId);

        return redirect()->back()->with('success', "Ulasan telah dihapus permanen dari sistem.");
    }

    private function syncPackageRating(int $packageId): void
    {
        $package = Package::find($packageId);
        if ($package) {
            $visibleReviews = Review::where('package_id', $packageId)
                ->where('is_approved', true)
                ->where('is_hidden', false);

            $count = $visibleReviews->count();
            $avg = $count > 0 ? $visibleReviews->avg('rating') : 5.0;

            $package->update([
                'total_reviews' => $count,
                'rating' => round($avg, 2),
            ]);
        }
    }
}
