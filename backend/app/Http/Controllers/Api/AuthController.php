<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Real login using Sanctum
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        $token = $user->createToken('mobile_app')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
            'user' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
            ]
        ]);
    }

    /**
     * Real registration
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|unique:users',
                'password' => 'required|string|min:6',
                'home_address' => 'nullable|string',
                'home_lat' => 'nullable|numeric',
                'home_lng' => 'nullable|numeric',
                'national_id' => 'nullable|string|unique:users',
            ]);

            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'home_address' => $request->home_address,
                'home_lat' => $request->home_lat,
                'home_lng' => $request->home_lng,
                'national_id' => $request->national_id,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('mobile_app')->plainTextToken;

            return response()->json([
                'message' => 'تم إنشاء الحساب بنجاح',
                'token' => $token,
                'user' => [
                    'name' => $user->name,
                    'phone' => $user->phone,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'بيانات التحقق غير صحيحة',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Registration Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'حدث خطأ في السيرفر: ' . $e->getMessage()
            ], 500);
        }
    }
}
