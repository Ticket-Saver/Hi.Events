<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $query = $event->tickets();

        // Búsqueda en múltiples campos
        if ($request->has('query')) {
            $searchQuery = $request->get('query');
            $query->where(function($q) use ($searchQuery) { 
                $q->where('title', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('position', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('seat_number', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('section', 'LIKE', "%{$searchQuery}%");
            });
        }

        // Ordenamiento
        if ($request->has('sort_by') && $request->sort_by) {
            $query->orderBy($request->sort_by, $request->sort_direction ?? 'asc');
        }

        // Paginación
        return $query->paginate($request->get('per_page', 20));
    }
} 