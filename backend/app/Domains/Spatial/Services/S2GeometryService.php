<?php

namespace App\Domains\Spatial\Services;

use S2\S2LatLng;
use S2\S2CellId;
use OpenLocationCode\OpenLocationCode;

/**
 * Service class for handling Google S2 Geometry and Digital Addressing logic.
 */
class S2GeometryService
{
    /**
     * Convert Latitude and Longitude to an S2 Token string at an optimal zoom level.
     */
    public function latLngToToken(float $latitude, float $longitude, int $level = 21): string
    {
        // Convert lat/lng to S2LatLng
        $latLng = S2LatLng::fromDegrees($latitude, $longitude);
        
        // Convert to S2CellId
        $cellId = S2CellId::fromLatLng($latLng);
        
        // Return token at the desired level
        $parent = $cellId->parent($level);
        $id = $parent->id;

        if ($id == 0) {
            return "X";
        }

        // Convert 64-bit ID to hex and strip trailing zeros (S2 Token format)
        $hex = str_pad(dechex($id), 16, '0', STR_PAD_LEFT);
        return rtrim($hex, '0');
    }

    /**
     * Generate a Plus Code for a given latitude and longitude.
     */
    public function generatePlusCode(float $latitude, float $longitude): string
    {
        return OpenLocationCode::encode($latitude, $longitude);
    }

    /**
     * Get bounding box or related cells for a neighborhood search.
     */
    public function getCellTokensForRadius(float $latitude, float $longitude, int $radiusMeters): array
    {
        // Placeholder for real spatial radius logic
        return [$this->latLngToToken($latitude, $longitude)];
    }

    /**
     * Decode an S2 Token back to a lat/lng center point.
     */
    public function decodeToken(string $token): array
    {
        $cellId = S2CellId::fromToken($token);
        $latLng = $cellId->toLatLng();

        return [
            'lat' => $latLng->latDegrees(),
            'lng' => $latLng->lngDegrees(),
        ];
    }

    /**
     * Decode a Plus Code (Open Location Code) back to a lat/lng bounding box + center.
     */
    public function decodePlusCode(string $plusCode): array
    {
        $area = OpenLocationCode::decode($plusCode);

        return [
            'lat'         => $area->latitudeCenter,
            'lng'         => $area->longitudeCenter,
            'lat_lo'      => $area->latitudeLo,
            'lng_lo'      => $area->longitudeLo,
            'lat_hi'      => $area->latitudeHi,
            'lng_hi'      => $area->longitudeHi,
            'code_length' => $area->codeLength,
        ];
    }
}
