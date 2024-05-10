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
}
