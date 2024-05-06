<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

final class EventController extends Controller
{
    public function index() {
        $events = Event::all()->sortByDesc('start');
        return view('events', ['events' => $events]);
    }
}
