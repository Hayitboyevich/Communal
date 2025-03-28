<?php

namespace Modules\Water\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->user ?  UserResource::make($this->user) : null,
            'first6' => $this->first6,
            'last4' =>$this->last4,
            'expMonth' => $this->expMonth,
            'expYear' => $this->expYear,
            'status' => $this->status
        ];
    }
}
