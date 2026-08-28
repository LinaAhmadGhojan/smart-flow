<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OfferController extends Controller
{
    /**
     * Display a listing of active offers with their products.
     */
    public function index()
    {
        try {
            $offers = Offer::with('product.category')
                ->whereHas('product', function ($q) {
                    // Public listing: hide offers whose product is admin-only
                    // Admin still sees all when authenticated via Bearer
                    if (!auth('sanctum')->user()) {
                        $q->where('is_visible', true);
                    }
                })
                ->get();
            
            // Format offers for both admin and client views
            $formattedOffers = $offers->map(function ($offer) {
                $product = $offer->product;
                $originalPrice = (float) $product->price;
                $discountedPrice = $offer->discount_price ?? ($originalPrice * (1 - $offer->discount_percentage / 100));
                
                return [
                    'id' => $offer->id,
                    'product_id' => $offer->product_id,
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'name_ar' => $product->name_ar,
                        'image' => $product->image,
                    ],
                    // Client view data
                    'name' => $product->name,
                    'name_ar' => $product->name_ar,
                    'brand' => $product->brand,
                    'image' => $product->image,
                    'in_stock' => $product->in_stock,
                    'category_id' => $product->category_id,
                    'features' => $product->features,
                    'description' => $product->description,
                    'description_ar' => $product->description_ar,
                    'whatsapp_message' => $product->whatsapp_message,
                    'original_price' => $originalPrice,
                    'discounted_price' => $discountedPrice,
                    'discount_percentage' => $offer->discount_percentage,
                    'offer_description' => $offer->offer_description,
                    'offer_description_ar' => $offer->offer_description_ar,
                    'start_date' => $offer->start_date,
                    'end_date' => $offer->end_date,
                    'is_active' => $offer->is_active,
                ];
            });
            
            return response()->json(['offers' => $formattedOffers], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created offer in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'discount_price' => 'nullable|numeric|min:0',
            'offer_description' => 'nullable|string',
            'offer_description_ar' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        try {
            $offer = Offer::create($validated);
            return response()->json(['data' => $offer->load('product')], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified offer.
     */
    public function show($id)
    {
        try {
            $offer = Offer::with('product')->findOrFail($id);
            return response()->json(['data' => $offer], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Offer not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified offer in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $offer = Offer::findOrFail($id);

            $validated = $request->validate([
                'product_id' => 'sometimes|exists:products,id',
                'discount_percentage' => 'sometimes|numeric|min:0|max:100',
                'discount_price' => 'nullable|numeric|min:0',
                'offer_description' => 'nullable|string',
                'offer_description_ar' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'is_active' => 'boolean',
            ]);

            $offer->update($validated);
            return response()->json(['data' => $offer->load('product')], Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Offer not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified offer from storage.
     */
    public function destroy($id)
    {
        try {
            $offer = Offer::findOrFail($id);
            $offer->delete();
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Offer not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

