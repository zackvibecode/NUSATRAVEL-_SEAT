@extends('layouts.app')

@section('title', 'Hermes Sync')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black tracking-tight leading-none">Hermes Sync</h2>
            <p class="text-sm text-charcoal mt-2">
                For Hermes Agent only — Dropbox baca Excel, SeatWeb yang ubah data.
            </p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-brand-soft text-brand">
            AI import guide
        </span>
    </div>

    <div class="grid lg:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line lg:col-span-2">
            <h3 class="text-sm font-bold uppercase tracking-wider text-charcoal mb-4">Bagaimana ia jalan</h3>
            <ol class="space-y-4 text-sm">
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-brand text-white font-black text-sm flex items-center justify-center">1</span>
                    <div>
                        <p class="font-bold">Dropbox</p>
                        <p class="text-charcoal mt-0.5">Letak Excel trip + customer dalam folder <code class="text-xs bg-fog px-1.5 py-0.5 rounded">/SeatWeb/Incoming/</code></p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-brand text-white font-black text-sm flex items-center justify-center">2</span>
                    <div>
                        <p class="font-bold">Hermes (baca je)</p>
                        <p class="text-charcoal mt-0.5">Agent baca Excel / MCP Dropbox, map column → JSON. <strong>Tak tulis terus ke Neon.</strong></p>
                    </div>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-brand text-white font-black text-sm flex items-center justify-center">3</span>
                    <div>
                        <p class="font-bold">SeatWeb (ubah data)</p>
                        <p class="text-charcoal mt-0.5">Hermes POST ke import API → Laravel upsert package, departure, registration dalam Neon.</p>
                    </div>
                </li>
            </ol>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line">
            <h3 class="text-sm font-bold uppercase tracking-wider text-charcoal mb-4">Status API</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-xs font-semibold text-charcoal mb-1">Import token</p>
                    @if ($tokenConfigured)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-positive-soft text-positive">Configured</span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-brand-soft text-brand">Missing IMPORT_API_TOKEN</span>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold text-charcoal mb-1">Endpoint</p>
                    <code class="block text-xs bg-fog rounded-2xl px-3 py-2 break-all">{{ $importUrl }}</code>
                </div>
                <p class="text-xs text-charcoal leading-relaxed">
                    Set token di Render Environment. Hermes guna Bearer token yang sama — jangan paste password DB.
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-line mb-8">
        <h3 class="text-sm font-bold uppercase tracking-wider text-charcoal mb-4">Prompt / cron untuk Hermes</h3>
        <pre class="text-xs sm:text-sm bg-fog rounded-2xl p-4 overflow-x-auto whitespace-pre-wrap font-mono text-ink leading-relaxed">Every 5 minutes:
1. List new .xlsx/.csv in Dropbox /SeatWeb/Incoming/
2. Download + parse into packages, departures, registrations JSON
3. POST {{ $importUrl }}?dry_run=1
   Header: Authorization: Bearer SEATWEB_IMPORT_TOKEN
4. If OK, POST again without dry_run
5. Move file to /SeatWeb/Processed/</pre>
        <p class="text-xs text-charcoal mt-3">
            Hermes secrets: <code class="bg-fog px-1.5 py-0.5 rounded">SEATWEB_IMPORT_URL</code> +
            <code class="bg-fog px-1.5 py-0.5 rounded">SEATWEB_IMPORT_TOKEN</code>
        </p>
    </div>

    <div class="grid lg:grid-cols-2 gap-4 mb-8">
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line">
            <h3 class="text-sm font-bold uppercase tracking-wider text-charcoal mb-4">Excel → SeatWeb (apa yang diubah)</h3>
            <ul class="space-y-3 text-sm">
                <li class="flex gap-2">
                    <span class="font-bold text-brand">Package</span>
                    <span class="text-charcoal">nama trip, destinasi, description, status</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-bold text-brand">Departure</span>
                    <span class="text-charcoal">tarikh, seat, harga, airline</span>
                </li>
                <li class="flex gap-2">
                    <span class="font-bold text-brand">Registration</span>
                    <span class="text-charcoal">customer, phone, pax, need partner</span>
                </li>
            </ul>
            <p class="text-xs text-charcoal mt-4 leading-relaxed">
                Sync ulang tak duplicate: match package by name+destination, departure by date, registration by name+phone.
            </p>
        </div>

        <div class="bg-white rounded-3xl p-6 shadow-sm border border-line">
            <h3 class="text-sm font-bold uppercase tracking-wider text-charcoal mb-4">Staff vs Hermes</h3>
            <div class="space-y-3 text-sm">
                <div class="rounded-2xl bg-fog px-4 py-3">
                    <p class="font-bold">Staff (kau)</p>
                    <p class="text-charcoal mt-1">Edit manual kat Packages / Trips / Participants — UI biasa.</p>
                </div>
                <div class="rounded-2xl bg-fog px-4 py-3">
                    <p class="font-bold">Hermes sahaja</p>
                    <p class="text-charcoal mt-1">Baca Dropbox + POST import API. MCP/Dropbox tools untuk file — bukan untuk tekan butang UI.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-line overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-line flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wider text-charcoal">Recent import runs</h3>
            <span class="text-xs text-charcoal">Dari table import_runs</span>
        </div>
        @if ($recentRuns->isEmpty())
            <div class="px-6 py-10 text-sm text-charcoal text-center">
                Belum ada sync. Bila Hermes POST sekali, history keluar sini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-charcoal uppercase tracking-wider bg-fog/60">
                            <th class="px-6 py-3">When</th>
                            <th class="px-6 py-3">File</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Counts</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($recentRuns as $run)
                            <tr>
                                <td class="px-6 py-3 whitespace-nowrap">{{ $run->created_at?->format('d M Y H:i') }}</td>
                                <td class="px-6 py-3">
                                    <div class="font-medium">{{ $run->filename ?: '—' }}</div>
                                    <div class="text-xs text-charcoal truncate max-w-xs">{{ $run->dropbox_path }}</div>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-brand-soft text-brand">
                                        {{ $run->status }}@if ($run->dry_run) · dry-run@endif
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-xs text-charcoal">
                                    P {{ $run->packages_created }}/{{ $run->packages_updated }}
                                    · D {{ $run->departures_created }}/{{ $run->departures_updated }}
                                    · R {{ $run->registrations_created }}/{{ $run->registrations_updated }}
                                    · skip {{ $run->skipped }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <p class="text-xs text-charcoal">
        Full technical contract: repo file <code class="bg-fog px-1.5 py-0.5 rounded">docs/hermes-dropbox-sync.md</code>
        · sample JSON <code class="bg-fog px-1.5 py-0.5 rounded">docs/samples/dropbox-excel-import.sample.json</code>
    </p>
@endsection
