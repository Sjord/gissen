<!doctype html>
<html lang="nl">
    <head>
        <title>Evenementen</title>
    </head>
    <body>
        <h1>Evenementen</h1>
        <table>
            <tr>
                <th>datum</th>
                <th>naam</th>
                <th>locatie</th>
                @auth
                    <th>acties</th>
                @endauth
            </tr>
            @foreach ($events as $event)
                <tr>
                    <td>{{ $event->start }}</td>
                    @if ($event->website_url)
                        <td><a href="{{ $event->website_url }}">{{ $event->title }}</a></td>
                    @else
                        <td>{{ $event->title }}</td>
                    @endif
                    <td>{{ $event->location }}</td>
                        @auth
                        <td>
                            <a href="{{ route('events.edit', $event) }}">&#9998;</a>
                        </td>
                    @endauth
                </tr>
            @endforeach
        </table>
    </body>
</html>