<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;

final class EventController extends Controller
{
    public function index() {
        $yesterday = new Carbon('yesterday');
        $events = Event::query()->where('start', '>=', $yesterday)->oldest('start')->get();
        return view('events', ['events' => $events]);
    }

    public function create()
    {
        return view('create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'location' => 'required|string|max:255',
        ]);

        Event::create($validated);

        return redirect()->route('index')->with('success', 'Event created successfully!');
    }
}
