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
                    <td>{{ $event->title }}</td>
                    <td>{{ $event->location }}</td>
                </tr>
            @endforeach
        </table>
    </body>
</html>