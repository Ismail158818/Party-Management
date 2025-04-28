<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'title' => $this->title,
            'date' => $this->date,
            'location' => $this->location,
            'media_url' => $this->media ? asset('storage/' . $this->media) : null,
            'ticket_price' => $this->ticket_price,
            'tickets_limit' => $this->tickets_limit,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
