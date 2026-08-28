<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\CategoryRequest;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('id')->get();
        return response()->json($categories);
    }

    public function store(CategoryRequest $request)
    {
        $category = Category::create([
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'icon' => $request->icon,
            'description' => $request->description,
            'description_ar' => $request->description_ar,
        ]);

        return response()->json($category->loadCount('products'), 201);
    }

    public function show($id)
    {
        $category = Category::with(['products' => function ($query) {
            $query->orderByDesc('id');
        }])->withCount('products')->findOrFail($id);

        return response()->json($category);
    }

    public function update(CategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);

        $category->update([
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'icon' => $request->icon,
            'description' => $request->description,
            'description_ar' => $request->description_ar,
        ]);

        return response()->json($category->loadCount('products'));
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
