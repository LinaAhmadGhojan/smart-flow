<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'error' => 'البريد أو كلمة السر غير صحيحة'
            ], 401);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'userId' => $admin->id,
            'email' => $admin->email,
            'isAdmin' => true,
            'token' => $token,
        ]);
    }

    public function registerAdmin(Request $request)
    {
        // Simple check - in production use proper authorization
        if ($request->adminKey !== 'ADMIN_SECRET_KEY') {
            return response()->json([
                'error' => 'غير مصرح'
            ], 403);
        }

        $request->validate([
            'email' => 'required|email|unique:admins',
            'password' => 'required|min:8',
        ]);

        $admin = Admin::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'admin' => [
                'id' => $admin->id,
                'email' => $admin->email,
            ]
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'id' => $request->user()->id,
            'email' => $request->user()->email,
            'isAdmin' => true,
        ]);
    }
}
