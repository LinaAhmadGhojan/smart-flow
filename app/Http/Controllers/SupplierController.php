<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /** GET /api/admin/suppliers */
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('q')) {
            $search = $request->string('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->orderBy('name')->get());
    }

    /** POST /api/admin/suppliers */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json($supplier, 201);
    }

    /** PATCH /api/admin/suppliers/{supplier} */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $supplier->update($validated);

        return response()->json($supplier);
    }

    /** DELETE /api/admin/suppliers/{supplier} */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json(['message' => 'تم حذف المورد.']);
    }
}
