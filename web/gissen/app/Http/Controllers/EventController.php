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

    /**
     * Store a newly created event or update an existing one.
     */
    public function save(Request $request, Event $event = null)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'location' => 'required|string|max:255',
            'website_url' => 'url|max:2048', // Allow empty and validate if present
        ]);

        if ($event) {
            // We are updating an existing event
            $event->update($validatedData);
            $message = 'Event updated successfully!';
        } else {
            // We are creating a new event
            Event::create($validatedData);
            $message = 'Event created successfully!';
        }

        return redirect()->route('index')->with('success', $message);
    }

    public function edit(Event $event)
    {
        // The $event model is automatically injected by Laravel's route model binding
        return view('create', ['event' => $event]); // Reuse the create view, passing the event
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
