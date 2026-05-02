<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/login',
        operationId: 'authLogin',
        summary: 'تسجيل الدخول',
        description: 'تسجيل دخول المواطن عبر رقم الهاتف وكلمة المرور.',
        tags: ['Auth']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['phone', 'password'],
            properties: [
                new OA\Property(property: 'phone', type: 'string', example: '0500000000'),
                new OA\Property(property: 'password', type: 'string', example: 'password123')
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'تم تسجيل الدخول بنجاح')]
    #[OA\Response(response: 401, description: 'بيانات الدخول غير صحيحة')]
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

    #[OA\Post(
        path: '/api/register',
        operationId: 'authRegister',
        summary: 'إنشاء حساب جديد',
        description: 'إنشاء حساب مواطن جديد للحصول على توكن الوصول.',
        tags: ['Auth']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'phone', 'password'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'أحمد محمد'),
                new OA\Property(property: 'phone', type: 'string', example: '0500000001'),
                new OA\Property(property: 'password', type: 'string', example: 'password123'),
                new OA\Property(property: 'national_id', type: 'string', example: '1000000001')
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'تم إنشاء الحساب بنجاح')]
    #[OA\Response(response: 422, description: 'خطأ في التحقق من البيانات')]
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|unique:users',
                'password' => 'required|string|min:6',
                'birth_date' => 'nullable|date',
                'home_address' => 'nullable|string',
                'home_lat' => 'nullable|numeric',
                'home_lng' => 'nullable|numeric',
                'national_id' => 'nullable|string|unique:users',
            ]);

            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'birth_date' => $request->birth_date,
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
            \Illuminate\Support\Facades\Log::error('Registration Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'حدث خطأ في السيرفر، يرجى المحاولة لاحقاً'
            ], 500);
        }
    }
}
