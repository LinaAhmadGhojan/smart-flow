<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /** GET /api/admin/customers */
    public function index(Request $request)
    {
        $query = Customer::query();

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

    /** POST /api/admin/customers */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $customer = Customer::create($validated);

        return response()->json($customer, 201);
    }

    /** PATCH /api/admin/customers/{customer} */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    /** DELETE /api/admin/customers/{customer} */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json(['message' => 'تم حذف العميل.']);
    }
}
