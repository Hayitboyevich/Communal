<?php

namespace Modules\Apartment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'street_name' => $this->street_name,
            'street_id' => $this->street_id,
            'home_name' => $this->street_id,
        ];
    }
}
