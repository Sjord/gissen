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
            'website_url' => 'url',
        ]);

        Event::create($validated);

        return redirect()->route('index')->with('success', 'Event created successfully!');
    }

    public function ical() {
        $yesterday = new Carbon('yesterday');
        $events = Event::query()->where('start', '>=', $yesterday)->oldest('start')->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'CALSCALE:GREGORIAN',
            'PRODID:-//Gissen//NL',
        ];

        foreach ($events as $event) {
            $date = Carbon::parse($event->start)->format('Ymd');

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:event' . $event->id . '@gissen.nl';
            $lines[] = 'DTSTAMP:' . now()->format('Ymd\THis\Z');
            $lines[] = 'DTSTART;VALUE=DATE:' . $date;
            $lines[] = 'DTEND;VALUE=DATE:' . Carbon::parse($event->start)->addDay()->format('Ymd');
            $lines[] = 'SUMMARY:' . $this->escapeString($event->title);
            $lines[] = 'LOCATION:' . $this->escapeString($event->location);
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        $calendarContent = implode("\r\n", $lines);

        return response($calendarContent, 200)
            ->header('Content-Type', 'text/calendar')
            ->header('Content-Disposition', 'attachment; filename="ical.ics"');
    }

    private function escapeString($string)
    {
        return addcslashes($string, ",;\\");
    }
}
