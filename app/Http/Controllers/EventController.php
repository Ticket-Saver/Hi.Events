<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // ... existing validation rules ...
            'tipoticket' => 'required|in:general,enumerado',
            'map' => 'nullable|in:map1,map2',
        ]);

        // ... rest of the store logic ...
    }
} 