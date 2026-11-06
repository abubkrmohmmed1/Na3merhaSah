<?php

namespace App\Domains\Addressing\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Domains\Addressing\Models\AddressSubmission;
use App\Domains\Addressing\Models\Address;
use App\Domains\Addressing\DTOs\AddressSubmissionData;
use App\Domains\Spatial\Services\S2GeometryService;
use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;

class SubmitAddressAction
{
    public function execute(Request $request): AddressSubmission
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address_str' => 'required|string|max:500',
            'neighborhood' => 'nullable|string|max:255',
            'type' => 'required|in:personal,service',
            'service_layer' => 'nullable|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $s2Service = app(S2GeometryService::class);

        $data = AddressSubmissionData::fromRequest($validated, Auth::id(), $s2Service);
        $dataArray = $data->toArray();

        $linked = $this->autoLink($validated['lat'], $validated['lng']);
        $dataArray = array_merge($dataArray, $linked);

        $submission = AddressSubmission::create($dataArray);

        DB::statement(
            "UPDATE address_submissions SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?",
            [$data->lng, $data->lat, $submission->id]
        );

        return $submission;
    }

    private function autoLink(float $lat, float $lng): array
    {
        $hoodLayer = MapLayer::where('name', 'الأحياء')->first();
        $locLayer = MapLayer::where('name', 'المحليات')->first();
        $stateLayer = MapLayer::where('name', 'الولايات')->first();

        $result = ['state_id' => null, 'state_name' => null, 'locality_id' => null, 'locality_name' => null, 'neighborhood_id' => null, 'neighborhood_name' => null];

        if ($hoodLayer) {
            $hood = GeoFeature::where('layer_id', $hoodLayer->id)
                ->whereNotNull('geom')
                ->whereRaw("ST_Contains(geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))", [$lng, $lat])
                ->orderByRaw("ST_Area(geom) ASC")
                ->first();
            if ($hood) {
                $result['neighborhood_id'] = $hood->id;
                $result['neighborhood_name'] = $hood->properties['name'] ?? null;

                if ($locLayer) {
                    $loc = $hood->parent;
                    if (!$loc || $loc->layer_id !== $locLayer->id) {
                        $loc = GeoFeature::where('layer_id', $locLayer->id)
                            ->whereNotNull('geom')
                            ->whereRaw("ST_Contains(geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))", [$lng, $lat])
                            ->orderByRaw("ST_Area(geom) ASC")
                            ->first();
                    }
                    if ($loc) {
                        $result['locality_id'] = $loc->id;
                        $result['locality_name'] = $loc->properties['name'] ?? null;

                        if ($stateLayer) {
                            $state = $loc->parent;
                            if (!$state || $state->layer_id !== $stateLayer->id) {
                                $state = GeoFeature::where('layer_id', $stateLayer->id)
                                    ->whereNotNull('geom')
                                    ->whereRaw("ST_Contains(geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))", [$lng, $lat])
                                    ->orderByRaw("ST_Area(geom) ASC")
                                    ->first();
                            }
                            if ($state) {
                                $result['state_id'] = $state->id;
                                $result['state_name'] = $state->properties['name'] ?? null;
                            }
                        }
                    }
                }
            } elseif ($locLayer) {
                $loc = GeoFeature::where('layer_id', $locLayer->id)
                    ->whereNotNull('geom')
                    ->whereRaw("ST_Contains(geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))", [$lng, $lat])
                    ->orderByRaw("ST_Area(geom) ASC")
                    ->first();
                if ($loc) {
                    $result['locality_id'] = $loc->id;
                    $result['locality_name'] = $loc->properties['name'] ?? null;

                    if ($stateLayer) {
                        $state = $loc->parent;
                        if (!$state || $state->layer_id !== $stateLayer->id) {
                            $state = GeoFeature::where('layer_id', $stateLayer->id)
                                ->whereNotNull('geom')
                                ->whereRaw("ST_Contains(geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))", [$lng, $lat])
                                ->orderByRaw("ST_Area(geom) ASC")
                                ->first();
                        }
                        if ($state) {
                            $result['state_id'] = $state->id;
                            $result['state_name'] = $state->properties['name'] ?? null;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function approve(AddressSubmission $submission): Address
    {
        $address = Address::create([
            'address_str' => $submission->address_str,
            'neighborhood' => $submission->neighborhood_name ?? $submission->neighborhood,
            'type' => $submission->type === 'personal' ? 'residential' : $submission->service_layer,
            's2_cell_id' => $submission->s2_cell_id,
            'plus_code' => $submission->plus_code,
            'metadata' => [
                'submitted_by' => $submission->user_id,
                'submission_id' => $submission->id,
                'phone' => $submission->phone,
                'notes' => $submission->notes,
                'state_id' => $submission->state_id,
                'locality_id' => $submission->locality_id,
            ],
            'is_verified' => true,
        ]);

        DB::statement(
            "UPDATE addresses SET location = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?",
            [$submission->lng, $submission->lat, $address->id]
        );

        $submission->approve();

        return $address;
    }

    public function reject(AddressSubmission $submission, string $reason): void
    {
        $submission->reject($reason);
    }
}