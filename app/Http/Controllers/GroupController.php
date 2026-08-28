<?php

namespace App\Http\Controllers;

use App\Models\ProductGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GroupController extends Controller
{
    public function index()
    {
        $groups = ProductGroup::withCount(['products' => function ($query) {
            $query->where('is_visible', true);
        }])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json($groups);
    }

    public function show($id)
    {
        $group = ProductGroup::with(['products' => function ($query) {
            $query->where('is_visible', true)->with('category')->orderByDesc('id');
        }])->withCount(['products' => function ($query) {
            $query->where('is_visible', true);
        }])->findOrFail($id);

        return response()->json($group);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeImage($request->file('image'));
        }

        $group = ProductGroup::create($validated);

        return response()->json($group->loadCount('products'), Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $group = ProductGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeImage($request->file('image'));
        }

        $group->update($validated);

        return response()->json($group->fresh()->loadCount('products'));
    }

    public function destroy($id)
    {
        $group = ProductGroup::findOrFail($id);
        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Group deleted successfully',
        ]);
    }

    private function storeImage($image): string
    {
        $filename = time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
        $destinationPath = public_path('storage/groups');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $image->move($destinationPath, $filename);

        return '/storage/groups/' . $filename;
    }
}
