<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Addressing\Actions\ReverseGeocodeAction;
use App\Domains\Addressing\Actions\SearchAddressesAction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class AddressingController extends Controller
{
    #[OA\Post(
        path: '/api/address/reverse',
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
        path: '/api/address/search',
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
            'query' => 'required|string|min:3',
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
}
