@extends('layout')

@section('content')
@php
    // Determine if we are in edit mode by checking if $event is set and has an ID
    $isEditMode = isset($event) && $event->exists;
@endphp
<div class="container">
    <h2>{{ $isEditMode ? 'Edit Event' : 'Create New Event' }}</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $isEditMode ? route('events.update', $event) : route('store') }}" method="POST">
        @csrf
        @if ($isEditMode)
            @method('PUT') {{-- Method spoofing for PUT request --}}
        @endif

        <div class="mb-3">
            <label for="title" class="form-label">Event Title</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $isEditMode ? $event->title : '') }}" required>
        </div>

        <div class="mb-3">
            <label for="start" class="form-label">Start Date & Time</label>
            {{-- Format date for datetime-local input type: YYYY-MM-DDTHH:MM --}}
            @php
                $startValue = '';
                if (old('start')) {
                    $startValue = old('start');
                } elseif ($isEditMode && $event->start) {
                    $startValue = \Carbon\Carbon::parse($event->start)->format('Y-m-d\TH:i');
                }
            @endphp
            <input type="datetime-local" name="start" id="start" class="form-control" value="{{ $startValue }}" required>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" name="location" id="location" class="form-control" value="{{ old('location', $isEditMode ? $event->location : '') }}" required>
        </div>

        <div class="mb-3"> {{-- Added mb-3 for consistent spacing --}}
            <label for="website_url" class="form-label">Website URL (optional)</label>
            <input type="url" name="website_url" id="website_url" class="form-control" value="{{ old('website_url', $isEditMode ? $event->website_url : '') }}" placeholder="https://example.com">
        </div>

        <button type="submit" class="btn btn-primary">{{ $isEditMode ? 'Update Event' : 'Create Event' }}</button>
    </form>
</div>
@endsection
