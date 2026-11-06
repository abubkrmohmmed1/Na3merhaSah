<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Addressing\Actions\ReverseGeocodeAction;
use App\Domains\Addressing\Actions\SearchAddressesAction;
use App\Domains\Spatial\Services\S2GeometryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class AddressingController extends Controller
{
    #[OA\Post(
        path: '/api/v1/addresses/reverse',
        operationId: 'addressReverse',
        summary: 'جلب العنوان من الإحداثيات',
        description: 'تحويل خطوط الطول والعرض (lat, lng) إلى عنوان مقروء S2 Token.',
        tags: ['Addressing'],
        security: [['sanctum' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['lat', 'lng'],
            properties: [
                new OA\Property(property: 'lat', type: 'number', example: 24.7136),
                new OA\Property(property: 'lng', type: 'number', example: 46.6753)
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'نجاح')]
    #[OA\Response(response: 404, description: 'لم يتم العثور على عنوان')]
    public function reverse(Request $request, ReverseGeocodeAction $action): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $address = $action->execute(
            (float) $request->lat,
            (float) $request->lng
        );

        if (!$address) {
            return response()->json([
                'message' => 'No address found for these coordinates.',
            ], 404);
        }

        return response()->json([
            'data' => [
                's2_token' => $address->s2_cell_id,
                'address_str' => $address->address_str,
                'neighborhood' => $address->neighborhood,
                'confidence_score' => 1.0,
            ]
        ]);
    }

    #[OA\Get(
        path: '/api/v1/addresses/search',
        operationId: 'addressSearch',
        summary: 'البحث عن عنوان',
        description: 'البحث النصي عن العناوين في قاعدة البيانات الجغرافية.',
        tags: ['Addressing'],
        security: [['sanctum' => []]]
    )]
    #[OA\Parameter(name: 'query', in: 'query', required: true, description: 'كلمة البحث', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'lat', in: 'query', required: false, description: 'للبحث الأقرب للموقع', schema: new OA\Schema(type: 'number'))]
    #[OA\Parameter(name: 'lng', in: 'query', required: false, description: 'للبحث الأقرب للموقع', schema: new OA\Schema(type: 'number'))]
    #[OA\Response(response: 200, description: 'نتائج البحث')]
    public function search(Request $request, SearchAddressesAction $action): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $results = $action->execute(
            $request->query('query'),
            $request->has('lat') ? (float) $request->lat : null,
            $request->has('lng') ? (float) $request->lng : null
        );

        return response()->json([
            'data' => $results->map(fn($address) => [
                's2_token' => $address->s2_cell_id,
                'address_str' => $address->address_str,
                'neighborhood' => $address->neighborhood,
            ])
        ]);
    }

    #[OA\Get(
        path: '/api/v1/addresses/decode',
        operationId: 'addressDecode',
        summary: 'فك تشفير رمز العنوان إلى إحداثيات',
        description: 'يقبل Plus Code أو S2 Token ويُعيد الإحداثيات الجغرافية المقابلة له.',
        tags: ['Addressing'],
    )]
    #[OA\Parameter(name: 'code', in: 'query', required: true, description: 'Plus Code (مثال: 7G7W2222+22) أو S2 Token (مثال: 9461d4a)', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'type', in: 'query', required: false, description: 'نوع الرمز: plus_code أو s2_token (يُكتشف تلقائياً إذا لم يُحدَّد)', schema: new OA\Schema(type: 'string', enum: ['plus_code', 's2_token']))]
    #[OA\Response(response: 200, description: 'الإحداثيات المفككة')]
    #[OA\Response(response: 422, description: 'رمز غير صالح')]
    public function decode(Request $request, S2GeometryService $s2Service): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'type' => 'nullable|in:plus_code,s2_token',
        ]);

        $code = trim($request->code);
        $type = $request->type;

        // Auto-detect type if not provided
        if (!$type) {
            $type = str_contains($code, '+') ? 'plus_code' : 's2_token';
        }

        try {
            if ($type === 'plus_code') {
                $coords = $s2Service->decodePlusCode($code);
                return response()->json([
                    'type'   => 'plus_code',
                    'code'   => $code,
                    'data'   => $coords,
                ]);
            }

            // S2 Token
            $coords = $s2Service->decodeToken($code);
            return response()->json([
                'type' => 's2_token',
                'code' => $code,
                'data' => $coords,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'رمز العنوان غير صالح أو لا يمكن فك تشفيره.',
                'error'   => $e->getMessage(),
            ], 422);
        }
    }
}
