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
                </tr>
            @endforeach
        </table>
    </body>
</html>