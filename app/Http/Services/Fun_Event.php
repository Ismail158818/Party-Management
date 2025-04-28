<?php

namespace App\Http\Services;
use App\Models\Event;

class Fun_Event
{
    public function store($validated)
{
    $event = Event::where('title', $validated['title'])->first();
    if ($event) {
        return false; 
    }
    $data = Event::create([
        'title' => $validated['title'],
        'description' => $validated['description'],
        'date' => $validated['date'],
        'location' => $validated['location'],
        'media' => $validated['media'] ?? null,
        'tickect_price' => $validated['tickect_price'],
        'tickets_limit' => $validated['tickets_limit'],
    ]);
    return $data; 
} 
}
