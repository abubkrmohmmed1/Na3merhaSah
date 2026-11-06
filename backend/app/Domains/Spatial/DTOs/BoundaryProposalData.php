<?php

namespace App\Domains\Addressing\DTOs;

class BoundaryProposalData
{
    public function __construct(
        public readonly string $userId,
        public readonly string $proposalType,
        public readonly string $name,
        public readonly float $lat,
        public readonly float $lng,
        public readonly string $s2CellId,
        public readonly string $plusCode,
        public readonly ?string $stateId,
        public readonly ?string $stateName,
        public readonly ?string $localityId,
        public readonly ?string $localityName,
        public readonly ?string $description,
        public readonly ?string $phone,
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'proposal_type' => $this->proposalType,
            'name' => $this->name,
            'lat' => $this->lat,
            'lng' => $this->lng,
            's2_cell_id' => $this->s2CellId,
            'plus_code' => $this->plusCode,
            'state_id' => $this->stateId,
            'state_name' => $this->stateName,
            'locality_id' => $this->localityId,
            'locality_name' => $this->localityName,
            'description' => $this->description,
            'phone' => $this->phone,
            'status' => 'pending',
        ];
    }
}