<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TastingLogResource extends JsonResource
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
            'action' => $this->action,
            'tasted_at' => $this->tasted_at,
            'note' => $this->note,
        ];
    }
}
