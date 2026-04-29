<?php

namespace App\Domains\Addressing\Actions;

use App\Domains\Addressing\Models\Address;
use Illuminate\Database\Eloquent\Collection;

class SearchAddressesAction
{
    public function execute(string $query, ?float $lat = null, ?float $lng = null): Collection
    {
        $searchQuery = Address::query();

        // 1. Basic Text Search (Can be improved with tsvector indexing in Sprint 2 polish)
        $searchQuery->where('address_str', 'ILIKE', '%' . addcslashes($query, '%_') . '%')
            ->orWhere('neighborhood', 'ILIKE', '%' . addcslashes($query, '%_') . '%');

        // 2. If Lat/Lng is provided, order by proximity to make auto-complete smarter
        if ($lat && $lng) {
            $searchQuery->orderByRaw("location <-> ST_SetSRID(ST_MakePoint(?, ?), 4326)", [(float) $lng, (float) $lat]);
        }

        return $searchQuery->limit(10)->get();
    }
}
