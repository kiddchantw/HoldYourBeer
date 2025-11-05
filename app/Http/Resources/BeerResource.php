<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BeerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'style' => $this->style,
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'tasting_count' => $this->when(
                isset($this->tasting_count),
                fn() => $this->tasting_count
            ),
            'last_tasted_at' => $this->when(
                isset($this->last_tasted_at),
                fn() => $this->last_tasted_at
            ),
        ];
    }
}
