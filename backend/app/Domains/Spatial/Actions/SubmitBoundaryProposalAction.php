<?php

namespace App\Domains\Spatial\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Domains\Spatial\Models\BoundaryProposal;
use App\Domains\Spatial\Models\GeoFeature;
use App\Domains\Spatial\Models\MapLayer;
use App\Domains\Spatial\DTOs\BoundaryProposalData;
use App\Domains\Spatial\Services\S2GeometryService;

class SubmitBoundaryProposalAction
{
    public function submitNeighborhood(Request $request): BoundaryProposal
    {
        return $this->submit($request, 'neighborhood');
    }

    public function submitVillage(Request $request): BoundaryProposal
    {
        return $this->submit($request, 'village');
    }

    private function submit(Request $request, string $type): BoundaryProposal
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
        ]);

        $s2Service = app(S2GeometryService::class);

        $linked = $this->autoLink($validated['lat'], $validated['lng']);

        $data = new BoundaryProposalData(
            userId: Auth::id(),
            proposalType: $type,
            name: $validated['name'],
            lat: (float) $validated['lat'],
            lng: (float) $validated['lng'],
            s2CellId: $s2Service->latLngToToken((float) $validated['lat'], (float) $validated['lng']),
            plusCode: $s2Service->generatePlusCode((float) $validated['lat'], (float) $validated['lng']),
            stateId: $linked['state_id'] ?? null,
            stateName: $linked['state_name'] ?? null,
            localityId: $linked['locality_id'] ?? null,
            localityName: $linked['locality_name'] ?? null,
            description: $validated['description'] ?? null,
            phone: $validated['phone'] ?? null,
        );

        $proposal = BoundaryProposal::create($data->toArray());

        DB::statement(
            "UPDATE boundary_proposals SET geom = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?",
            [$validated['lng'], $validated['lat'], $proposal->id]
        );

        return $proposal;
    }

    private function autoLink(float $lat, float $lng): array
    {
        $localityLayer = MapLayer::where('name', 'المحليات')->first();
        if (!$localityLayer) return [];

        $locality = GeoFeature::where('layer_id', $localityLayer->id)
            ->whereNotNull('geom')
            ->whereRaw("ST_Contains(geom, ST_SetSRID(ST_MakePoint(?, ?), 4326))", [$lng, $lat])
            ->orderByRaw("ST_Area(geom) ASC")
            ->first();

        if (!$locality) return [];

        $result = [
            'locality_id' => $locality->id,
            'locality_name' => $locality->properties['name'] ?? null,
        ];

        $state = $locality->parent;
        if ($state) {
            $result['state_id'] = $state->id;
            $result['state_name'] = $state->properties['name'] ?? null;
        }

        return $result;
    }

    public function approve(BoundaryProposal $proposal): GeoFeature
    {
        $layerName = $proposal->proposal_type === 'neighborhood' ? 'الأحياء' : 'القرى والمدن';
        $layer = MapLayer::where('name', $layerName)->firstOrFail();

        $feature = GeoFeature::create([
            'layer_id' => $layer->id,
            'parent_id' => $proposal->locality_id,
            'properties' => [
                'name' => $proposal->name,
                'description' => $proposal->description,
                'submitted_by' => $proposal->user_id,
                'proposal_id' => $proposal->id,
            ],
            'status' => 'verified',
            'latitude' => $proposal->lat,
            'longitude' => $proposal->lng,
        ]);

        \App\Domains\Community\Models\UserAction::create([
            'user_id' => $proposal->user_id,
            'geo_feature_id' => $feature->id,
            'action_type' => 'add',
            'points' => \App\Domains\Community\Models\UserAction::pointsForAction('add'),
            'status' => 'active'
        ]);

        $proposal->approve([
            'feature_id' => $feature->id,
        ]);

        return $feature;
    }

    public function reject(BoundaryProposal $proposal, string $reason): void
    {
        $proposal->reject($reason);
    }
}