<?php

namespace App\Http\Controllers;

use App\Models\Engineer;
use Illuminate\Http\Request;

class EngineerController extends Controller
{
    /** GET /api/admin/engineers */
    public function index(Request $request)
    {
        $query = Engineer::query();

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

    /** POST /api/admin/engineers */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $engineer = Engineer::create($validated);

        return response()->json($engineer, 201);
    }

    /** PATCH /api/admin/engineers/{engineer} */
    public function update(Request $request, Engineer $engineer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $engineer->update($validated);

        return response()->json($engineer);
    }

    /** DELETE /api/admin/engineers/{engineer} */
    public function destroy(Engineer $engineer)
    {
        $engineer->delete();

        return response()->json(['message' => 'تم حذف المهندس.']);
    }
}
