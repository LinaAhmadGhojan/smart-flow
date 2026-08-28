<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\StorageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'group']);

        // Public visitors only see visible products; admin (Bearer token) sees all
        if (!$this->isAdminRequest($request)) {
            $query->visibleToPublic();
        }

        $products = $query->get()->map(fn (Product $product) => $this->productPayload($product));

        return response()->json(['products' => $products]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'category_id' => 'required|exists:categories,id',
            'group_id' => 'nullable|exists:product_groups,id',
            'features' => 'nullable|string',
            'in_stock' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'whatsapp_message' => 'nullable|string',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('storage/products');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $image->move($destinationPath, $filename);
            $imagePath = '/storage/products/' . $filename;
        }

        $features = [];
        if ($request->features) {
            $features = json_decode($request->features, true) ?? [];
        }

        $product = Product::create([
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'description' => $request->description,
            'description_ar' => $request->description_ar,
            'brand' => $request->brand,
            'price' => $request->price,
            'price_number' => $request->price,
            'image' => $imagePath,
            'in_stock' => $request->boolean('in_stock', true),
            'is_visible' => $request->boolean('is_visible', true),
            'category_id' => $request->category_id,
            'group_id' => $request->group_id ?: null,
            'features' => $features,
            'whatsapp_message' => $request->whatsapp_message,
        ]);

        return response()->json($this->productPayload($product->fresh(['category', 'group'])), 201);
    }

    public function show(Request $request, $id)
    {
        $product = Product::with(['category', 'group'])->findOrFail($id);

        if (!$product->is_visible && !$this->isAdminRequest($request)) {
            abort(404);
        }

        return response()->json($this->productPayload($product));
    }

    /** @return array<string, mixed> */
    private function productPayload(Product $product): array
    {
        $data = $product->toArray();
        $data['image'] = StorageUrl::toPublicUrl($product->getRawOriginal('image'));

        return $data;
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'category_id' => 'required|exists:categories,id',
            'group_id' => 'nullable|exists:product_groups,id',
            'features' => 'nullable|string',
            'in_stock' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'whatsapp_message' => 'nullable|string',
        ]);

        $imagePath = $product->getRawOriginal('image');
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->getRawOriginal('image')) {
                $oldImagePath = StorageUrl::toFilesystemPath($product->getRawOriginal('image'));
                if ($oldImagePath && file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            }
            $image = $request->file('image');
            $filename = time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('storage/products');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $image->move($destinationPath, $filename);
            $imagePath = '/storage/products/' . $filename;
        }

        $features = $product->features;
        if ($request->features) {
            $features = json_decode($request->features, true) ?? [];
        }
        $product->update([
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'description' => $request->description,
            'description_ar' => $request->description_ar,
            'brand' => $request->brand,
            'price' => $request->price,
            'price_number' => $request->price,
            'image' => $imagePath,
            'in_stock' => $request->boolean('in_stock', true),
            'is_visible' => $request->boolean('is_visible', true),
            'category_id' => $request->category_id,
            'group_id' => $request->group_id ?: null,
            'features' => $features,
            'whatsapp_message' => $request->whatsapp_message??'',
        ]);

        return response()->json($this->productPayload($product->fresh(['category', 'group'])));
    }

    private function isAdminRequest(Request $request): bool
    {
        return (bool) auth('sanctum')->user();
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // Delete image if exists
        if ($product->getRawOriginal('image')) {
            $imagePath = StorageUrl::toFilesystemPath($product->getRawOriginal('image'));
            if ($imagePath && file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }
        
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('storage/products');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $image->move($destinationPath, $filename);

            return response()->json([
                'image' => StorageUrl::toPublicUrl('/storage/products/' . $filename),
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
