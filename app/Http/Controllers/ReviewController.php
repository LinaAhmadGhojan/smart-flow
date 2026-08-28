<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** GET /api/reviews */
    public function index()
    {
        $reviews = Review::where('is_visible', true)->latest()->get()->map(fn ($r) => $this->format($r));

        return response()->json([
            'reviews' => $reviews,
            'average' => round($reviews->avg('rating'), 1),
            'count'   => $reviews->count(),
        ]);
    }

    /** POST /api/reviews */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reviewer_name'    => 'nullable|string|max:255',
            'reviewer_email'   => 'nullable|email|max:255',
            'reviewer_photo'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'reviewer_video'   => 'nullable|mimes:mp4,mov,avi,webm|max:51200',
            'rating'           => 'required|integer|min:1|max:5',
            'comment'          => 'required|string|max:2000',
        ]);

        $photoPath = null;
        if ($request->hasFile('reviewer_photo')) {
            $file = $request->file('reviewer_photo');
            $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = public_path('storage/reviews');
            if (!file_exists($dest)) mkdir($dest, 0755, true);
            $file->move($dest, $filename);
            $photoPath = '/storage/reviews/' . $filename;
        }

        $videoPath = null;
        if ($request->hasFile('reviewer_video')) {
            $file = $request->file('reviewer_video');
            $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = public_path('storage/reviews');
            if (!file_exists($dest)) mkdir($dest, 0755, true);
            $file->move($dest, $filename);
            $videoPath = '/storage/reviews/' . $filename;
        }

        $review = Review::create([
            'reviewer_name'    => $validated['reviewer_name'] ?? null,
            'reviewer_email'   => $validated['reviewer_email'] ?? null,
            'reviewer_photo'   => $photoPath,
            'reviewer_video'   => $videoPath,
            'rating'           => $validated['rating'],
            'comment'          => $validated['comment'],
            'is_visible'       => true,
            'source'           => 'customer',
        ]);

        return response()->json(['message' => 'شكراً على تقييمك!', 'review' => $this->format($review)], 201);
    }

    /** GET /api/admin/reviews */
    public function adminIndex(Request $request)
    {
        $query = Review::latest();
        if ($request->filled('visible')) {
            $query->where('is_visible', filter_var($request->visible, FILTER_VALIDATE_BOOLEAN));
        }
        $reviews = $query->get()->map(fn ($r) => $this->format($r));

        return response()->json([
            'reviews'       => $reviews,
            'total'         => $reviews->count(),
            'visible_count' => $reviews->where('is_visible', true)->count(),
            'pending_count' => $reviews->where('is_visible', false)->count(),
            'average'       => round($reviews->avg('rating'), 1),
        ]);
    }

    /** POST /api/admin/reviews */
    public function adminStore(Request $request)
    {
        $validated = $request->validate([
            'reviewer_name'    => 'nullable|string|max:255',
            'reviewer_email'   => 'nullable|email|max:255',
            'reviewer_photo'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'reviewer_video'   => 'nullable|mimes:mp4,mov,avi,webm|max:51200',
            'rating'           => 'required|integer|min:1|max:5',
            'comment'          => 'required|string|max:2000',
            'is_visible'       => 'boolean',
        ]);

        $photoPath = null;
        if ($request->hasFile('reviewer_photo')) {
            $file = $request->file('reviewer_photo');
            $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = public_path('storage/reviews');
            if (!file_exists($dest)) mkdir($dest, 0755, true);
            $file->move($dest, $filename);
            $photoPath = '/storage/reviews/' . $filename;
        }

        $videoPath = null;
        if ($request->hasFile('reviewer_video')) {
            $file = $request->file('reviewer_video');
            $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = public_path('storage/reviews');
            if (!file_exists($dest)) mkdir($dest, 0755, true);
            $file->move($dest, $filename);
            $videoPath = '/storage/reviews/' . $filename;
        }

        $review = Review::create([
            'reviewer_name'    => $validated['reviewer_name'] ?? null,
            'reviewer_email'   => $validated['reviewer_email'] ?? null,
            'reviewer_photo'   => $photoPath,
            'reviewer_video'   => $videoPath,
            'rating'           => $validated['rating'],
            'comment'          => $validated['comment'],
            'is_visible'       => $validated['is_visible'] ?? true,
            'source'           => 'admin',
        ]);

        return response()->json($this->format($review), 201);
    }

    /** PUT /api/admin/reviews/{review} */
    public function update(Request $request, Review $review)
    {
        $validated = $request->validate([
            'reviewer_name' => 'nullable|string|max:255',
            'reviewer_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'reviewer_video' => 'nullable|mimes:mp4,mov,avi,webm|max:51200',
            'rating'        => 'required|integer|min:1|max:5',
            'comment'       => 'required|string|max:2000',
        ]);

        $data = [
            'reviewer_name' => $validated['reviewer_name'] ?? null,
            'rating'        => $validated['rating'],
            'comment'       => $validated['comment'],
        ];

        if ($request->hasFile('reviewer_photo')) {
            $file = $request->file('reviewer_photo');
            $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = public_path('storage/reviews');
            if (!file_exists($dest)) mkdir($dest, 0755, true);
            $file->move($dest, $filename);
            $data['reviewer_photo'] = '/storage/reviews/' . $filename;
        }

        if ($request->hasFile('reviewer_video')) {
            $file = $request->file('reviewer_video');
            $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $dest = public_path('storage/reviews');
            if (!file_exists($dest)) mkdir($dest, 0755, true);
            $file->move($dest, $filename);
            $data['reviewer_video'] = '/storage/reviews/' . $filename;
        }

        $review->update($data);

        return response()->json($this->format($review));
    }

    /** PATCH /api/admin/reviews/{review} */
    public function toggleVisibility(Request $request, Review $review)
    {
        $review->update(['is_visible' => (bool) $request->input('is_visible', !$review->is_visible)]);
        return response()->json($this->format($review));
    }

    /** DELETE /api/admin/reviews/{review} */
    public function destroy(Review $review)
    {
        $review->delete();
        return response()->json(['message' => 'تم حذف التقييم.']);
    }

    private function format(Review $review): array
    {
        return [
            'id'               => $review->id,
            'reviewer_name'    => $review->reviewer_name,
            'display_name'     => $review->display_name,
            'reviewer_photo'   => $review->reviewer_photo,
            'reviewer_video'   => $review->reviewer_video,
            'rating'           => $review->rating,
            'comment'          => $review->comment,
            'is_visible'       => $review->is_visible,
            'source'           => $review->source,
            'created_at'       => $review->created_at?->format('Y-m-d'),
        ];
    }
}
