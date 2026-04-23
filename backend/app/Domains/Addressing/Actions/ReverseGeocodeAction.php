<?php

namespace App\Domains\Addressing\Actions;

use App\Domains\Addressing\Models\Address;
use App\Domains\Spatial\Services\S2GeometryService;

class ReverseGeocodeAction
{
    public function __construct(
        protected S2GeometryService $s2Service
    ) {}

    public function execute(float $lat, float $lng): ?Address
    {
        // 1. Convert Lat/Lng to S2 Token (Level 21 as per our standard / ~3m)
        $token = $this->s2Service->latLngToToken($lat, $lng, 21);

        // 2. Try to find the exact address match in our database
        $address = Address::where('s2_cell_id', $token)->first();

        // 3. Fallback: If no exact match (newly tagged or needs spatial query),
        // we might eventually perform a PostGIS distance query.
        // For the start of Sprint 2, we rely on the S2 Spatial index.
        if (!$address) {
            // Find nearest using PostGIS if S2 exact match fails
            $address = Address::orderByRaw("location <-> 'SRID=4326;POINT($lng $lat)'::geometry")
                ->first();
        }

        return $address;
    }
}
