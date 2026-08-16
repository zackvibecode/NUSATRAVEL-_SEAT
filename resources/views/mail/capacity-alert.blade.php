<x-mail::message>
# Trips Nearly Full

These upcoming trips have **{{ $threshold }} or fewer seats** left. Follow up with customers or add capacity before they sell out.

<x-mail::table>
    <tr>
        <th>Package</th>
        <th>Departure</th>
        <th>Seats Left</th>
        <th>Status</th>
    </tr>
    @foreach ($trips as $trip)
        <tr>
            <td>{{ $trip->package?->name ?? '—' }}</td>
            <td>{{ $trip->departure_date->format('d M Y') }}</td>
            <td>{{ $trip->available_seats }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $trip->status_label)) }}</td>
        </tr>
    @endforeach
</x-mail::table>

Thanks,
{{ config('app.name') }}
</x-mail::message>
