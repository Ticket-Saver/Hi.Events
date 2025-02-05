<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'organizer_id' => 'required|exists:organizers,id',
            'tipoticket' => 'required|in:general,enumerado',
            'map' => 'nullable|in:map1,map2',
        ]);

        $event = Event::create($validatedData);

        return response()->json([
            'data' => $event,
            'message' => 'Event created successfully'
        ], 201);
    }
} 