<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Manifest — {{ $departure->package->name }}</title>
        @include('partials.assets')
        <style>
            @media print {
                body { background: white !important; }
                .no-print { display: none !important; }
                .page-break { page-break-before: always; }
            }
            @page { margin: 1cm; }
        </style>
    </head>
    <body class="bg-white text-ink antialiased">
        <div class="max-w-4xl mx-auto p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8 pb-6 border-b-2 border-ink">
                <div>
                    <h1 class="text-2xl font-black tracking-tight">{{ $departure->package->name }}</h1>
                    <p class="text-sm text-charcoal mt-1">Departure Manifest</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold">{{ $departure->departure_date->format('d M Y') }}</p>
                    <p class="text-xs text-charcoal">to {{ $departure->return_date->format('d M Y') }}</p>
                </div>
            </div>

            <!-- Trip Info -->
            <div class="grid grid-cols-4 gap-4 mb-8 text-sm">
                <div>
                    <p class="text-xs text-charcoal uppercase font-semibold">Destination</p>
                    <p class="font-bold">{{ $departure->package->destination }}</p>
                </div>
                <div>
                    <p class="text-xs text-charcoal uppercase font-semibold">Airline</p>
                    <p class="font-bold">{{ $departure->airline ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-charcoal uppercase font-semibold">Total Seats</p>
                    <p class="font-bold">{{ $departure->total_seats }}</p>
                </div>
                <div>
                    <p class="text-xs text-charcoal uppercase font-semibold">Registered</p>
                    <p class="font-bold">{{ $departure->registered_pax }}</p>
                </div>
            </div>

            <!-- Passenger List -->
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="border-b-2 border-ink">
                        <th class="text-left py-3 font-bold uppercase text-xs tracking-wider">#</th>
                        <th class="text-left py-3 font-bold uppercase text-xs tracking-wider">Name</th>
                        <th class="text-left py-3 font-bold uppercase text-xs tracking-wider">Phone</th>
                        <th class="text-center py-3 font-bold uppercase text-xs tracking-wider">Pax</th>
                        <th class="text-center py-3 font-bold uppercase text-xs tracking-wider">Payment</th>
                        <th class="text-left py-3 font-bold uppercase text-xs tracking-wider">Partner</th>
                        <th class="text-left py-3 font-bold uppercase text-xs tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($registrations as $index => $reg)
                        <tr class="border-b border-line">
                            <td class="py-3 text-charcoal">{{ $index + 1 }}</td>
                            <td class="py-3 font-semibold">{{ $reg->name }}</td>
                            <td class="py-3 text-charcoal">{{ $reg->phone ?? '—' }}</td>
                            <td class="py-3 text-center font-bold">{{ $reg->pax }}</td>
                            <td class="py-3 text-center">
                                <span class="text-xs font-bold uppercase {{ $reg->payment_status === 'paid' ? 'text-positive' : ($reg->payment_status === 'deposit' ? 'text-warning' : 'text-brand') }}">
                                    {{ $reg->payment_label }}
                                </span>
                            </td>
                            <td class="py-3 text-charcoal">{{ $reg->need_partner ? $reg->partner_label : '—' }}</td>
                            <td class="py-3 text-charcoal text-xs">{{ $reg->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-charcoal">No passengers registered.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-line flex items-center justify-between text-xs text-charcoal">
                <p>Generated: {{ now()->format('d M Y H:i') }}</p>
                <p>Total Pax: {{ $departure->registered_pax }} / {{ $departure->total_seats }}</p>
            </div>

            <!-- Print button -->
            <div class="mt-8 text-center no-print">
                <button onclick="window.print()" class="bg-brand hover:bg-brand-hover text-white font-bold rounded-full px-8 py-3 text-sm transition-all">
                    Print Manifest
                </button>
                <a href="{{ route('departures.show', $departure) }}" class="ml-4 text-sm font-bold text-charcoal hover:text-ink">
                    ← Back to Trip
                </a>
            </div>
        </div>
    </body>
</html>
