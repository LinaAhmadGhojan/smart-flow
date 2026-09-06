<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\StorageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'data_sheet' => 'nullable|file|mimes:pdf|max:20480',
            'category_id' => 'required|exists:categories,id',
            'group_id' => 'nullable|exists:product_groups,id',
            'features' => 'nullable|string',
            'in_stock' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'whatsapp_message' => 'nullable|string',
        ]);

        $imagePath = $request->hasFile('image')
            ? $this->storeUpload($request->file('image'), 'products')
            : null;

        $dataSheetPath = $request->hasFile('data_sheet')
            ? $this->storeUpload($request->file('data_sheet'), 'products/datasheets')
            : null;

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
            'data_sheet' => $dataSheetPath,
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
        $data['data_sheet'] = StorageUrl::toPublicUrl($product->getRawOriginal('data_sheet'));

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'data_sheet' => 'nullable|file|mimes:pdf|max:20480',
            'remove_data_sheet' => 'nullable|boolean',
            'category_id' => 'required|exists:categories,id',
            'group_id' => 'nullable|exists:product_groups,id',
            'features' => 'nullable|string',
            'in_stock' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'whatsapp_message' => 'nullable|string',
        ]);

        $imagePath = $product->getRawOriginal('image');
        if ($request->hasFile('image')) {
            $this->deleteStoredFile($imagePath);
            $imagePath = $this->storeUpload($request->file('image'), 'products');
        }

        $dataSheetPath = $product->getRawOriginal('data_sheet');
        if ($request->boolean('remove_data_sheet') && !$request->hasFile('data_sheet')) {
            $this->deleteStoredFile($dataSheetPath);
            $dataSheetPath = null;
        }
        if ($request->hasFile('data_sheet')) {
            $this->deleteStoredFile($dataSheetPath);
            $dataSheetPath = $this->storeUpload($request->file('data_sheet'), 'products/datasheets');
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
            'data_sheet' => $dataSheetPath,
            'in_stock' => $request->boolean('in_stock', true),
            'is_visible' => $request->boolean('is_visible', true),
            'category_id' => $request->category_id,
            'group_id' => $request->group_id ?: null,
            'features' => $features,
            'whatsapp_message' => $request->whatsapp_message ?? '',
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

        $this->deleteStoredFile($product->getRawOriginal('image'));
        $this->deleteStoredFile($product->getRawOriginal('data_sheet'));

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($request->hasFile('image')) {
            $path = $this->storeUpload($request->file('image'), 'products');

            return response()->json([
                'image' => StorageUrl::toPublicUrl($path),
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }

    private function storeUpload(UploadedFile $file, string $folder): string
    {
        $filename = time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('storage/' . $folder);
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        $file->move($destinationPath, $filename);

        return '/storage/' . $folder . '/' . $filename;
    }

    private function deleteStoredFile(?string $storedPath): void
    {
        if (!$storedPath) {
            return;
        }
        $absolute = StorageUrl::toFilesystemPath($storedPath);
        if ($absolute && file_exists($absolute)) {
            @unlink($absolute);
        }
    }
}
