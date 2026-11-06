<?php

namespace App\Domains\Addressing\DTOs;

use App\Domains\Spatial\Services\S2GeometryService;

class AddressSubmissionData
{
    public function __construct(
        public readonly string $userId,
        public readonly string $name,
        public readonly string $addressStr,
        public readonly ?string $neighborhood,
        public readonly string $type,
        public readonly ?string $serviceLayer,
        public readonly float $lat,
        public readonly float $lng,
        public readonly string $s2CellId,
        public readonly string $plusCode,
        public readonly ?string $phone,
        public readonly ?string $notes,
    ) {}

    public static function fromRequest(array $data, string $userId, S2GeometryService $s2Service): self
    {
        return new self(
            userId: $userId,
            name: $data['name'],
            addressStr: $data['address_str'],
            neighborhood: $data['neighborhood'] ?? null,
            type: $data['type'],
            serviceLayer: $data['service_layer'] ?? null,
            lat: (float) $data['lat'],
            lng: (float) $data['lng'],
            s2CellId: $s2Service->latLngToToken((float) $data['lat'], (float) $data['lng']),
            plusCode: $s2Service->generatePlusCode((float) $data['lat'], (float) $data['lng']),
            phone: $data['phone'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'name' => $this->name,
            'address_str' => $this->addressStr,
            'neighborhood' => $this->neighborhood,
            'type' => $this->type,
            'service_layer' => $this->serviceLayer,
            'lat' => $this->lat,
            'lng' => $this->lng,
            's2_cell_id' => $this->s2CellId,
            'plus_code' => $this->plusCode,
            'phone' => $this->phone,
            'notes' => $this->notes,
            'status' => 'pending',
        ];
    }
}