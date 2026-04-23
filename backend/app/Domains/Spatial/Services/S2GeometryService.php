<?php

namespace App\Domains\Spatial\Services;

use S2\S2LatLng;
use S2\S2CellId;

/**
 * Service class for handling Google S2 Geometry logic mapping using real library.
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
        return $cellId->parent($level)->toToken();
    }

    /**
     * Get bounding box or related cells for a neighborhood search.
     */
    public function getCellTokensForRadius(float $latitude, float $longitude, int $radiusMeters): array
    {
        // Placeholder for real spatial radius logic
        return [$this->latLngToToken($latitude, $longitude)];
    }
}
