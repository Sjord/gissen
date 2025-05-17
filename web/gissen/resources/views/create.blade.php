@extends('layout')

@section('content')
<div class="container">
    <h2>Create New Event</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="title" class="form-label">Event Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="start" class="form-label">Start Date & Time</label>
            <input type="datetime-local" name="start" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" name="location" class="form-control" required>
        </div>

        <div>
            <label for="website_url">Website URL (optional)</label>
            <input type="url" name="website_url" id="website_url" value="{{ old('website_url') }}" placeholder="https://example.com">
        </div>

        <button type="submit" class="btn btn-primary">Create Event</button>
    </form>
</div>
@endsection
