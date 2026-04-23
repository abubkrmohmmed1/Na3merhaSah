<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domains\Addressing\Actions\ReverseGeocodeAction;
use App\Domains\Addressing\Actions\SearchAddressesAction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AddressingController extends Controller
{
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
