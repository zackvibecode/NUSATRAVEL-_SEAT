<?php

namespace App\Services;

use App\Models\Departure;
use App\Models\Package;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HermesDataService
{
    public function __construct(private HermesSeatActivityLogger $activityLogger) {}

    /**
     * Destructive chat intents require an explicit "confirm <command>" follow-up
     * so a misparsed or accidental message cannot delete live data.
     *
     * @return array{matched: bool, id: int}
     */
    private function parseDestructiveIntent(string $lower): array
    {
        if (preg_match('/\b(delete|padam|hapus|archive|arsib)\b.*\b(package|pakej)\s+(\d+)/u', $lower, $m)
            || preg_match('/\b(package|pakej)\s+(\d+).*\b(delete|padam|hapus|archive|arsib)\b/u', $lower, $m2)) {
            return ['matched' => true, 'id' => (int) ($m[3] ?? $m2[2] ?? 0)];
        }

        if (preg_match('/\b(cancel|batal)\b.*\b(trip|departure)\s+(\d+)/u', $lower, $m)
            || preg_match('/\b(trip|departure)\s+(\d+).*\b(cancel|batal)\b/u', $lower, $m2)) {
            return ['matched' => true, 'id' => (int) ($m[3] ?? $m2[2] ?? 0)];
        }

        if (preg_match('/\b(padam|delete|hapus)\b.*\b(pax|registration|customer)\s+(\d+)/u', $lower, $m)
            || preg_match('/\b(pax|registration)\s+(\d+).*\b(padam|delete|hapus)\b/u', $lower, $m2)) {
            return ['matched' => true, 'id' => (int) ($m[3] ?? $m2[2] ?? 0)];
        }

        return ['matched' => false, 'id' => 0];
    }

    public function overview(): array
    {
        $packages = Package::query()->count();
        $departures = Departure::query()->count();
        $registrations = Registration::query()->count();
        $pax = (int) Registration::query()->sum('pax');

        return [
            'packages' => $packages,
            'departures' => $departures,
            'registrations' => $registrations,
            'total_pax' => $pax,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listPackages(?string $search = null): array
    {
        $query = Package::query()->withCount('departures')->orderBy('name');

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('destination', 'like', '%'.$search.'%');
            });
        }

        return $query->get()->map(fn (Package $p) => $this->packagePayload($p))->all();
    }

    public function getPackage(int $id): ?array
    {
        $package = Package::query()->with(['departures.registrations'])->find($id);

        return $package ? $this->packagePayload($package, true) : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createPackage(array $data): Package
    {
        return Package::create([
            'name' => trim((string) $data['name']),
            'destination' => trim((string) $data['destination']),
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePackage(Package $package, array $data): Package
    {
        $package->update(array_filter([
            'name' => isset($data['name']) ? trim((string) $data['name']) : null,
            'destination' => isset($data['destination']) ? trim((string) $data['destination']) : null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? null,
        ], fn ($v) => $v !== null));

        return $package->fresh();
    }

    /**
     * Hard-delete a package (cascades departures + registrations).
     *
     * @return array<string, mixed> Payload captured before delete
     */
    public function deletePackage(Package $package): array
    {
        $payload = $this->packagePayload($package);
        $package->delete();

        return $payload;
    }

    /** @deprecated Use deletePackage() — kept as alias for callers that still say "archive". */
    public function archivePackage(Package $package): array
    {
        return $this->deletePackage($package);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listDepartures(?string $search = null): array
    {
        $query = Departure::query()->with('package', 'registrations')->orderByDesc('departure_date');

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('package', function ($p) use ($search) {
                    $p->where('name', 'like', '%'.$search.'%')
                        ->orWhere('destination', 'like', '%'.$search.'%');
                })->orWhere('airline', 'like', '%'.$search.'%');
            });
        }

        return $query->get()->map(fn (Departure $d) => $this->departurePayload($d))->all();
    }

    public function getDeparture(int $id): ?array
    {
        $departure = Departure::query()->with('package', 'registrations')->find($id);

        return $departure ? $this->departurePayload($departure, true) : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDeparture(array $data): Departure
    {
        $departure = Departure::create([
            'package_id' => (int) $data['package_id'],
            'departure_date' => $data['departure_date'],
            'return_date' => $data['return_date'],
            'total_seats' => max(1, (int) ($data['total_seats'] ?? 1)),
            'price' => $data['price'] ?? null,
            'airline' => $data['airline'] ?? null,
            'status' => $data['status'] ?? 'open',
            'notes' => $data['notes'] ?? null,
        ]);

        $departure->load('package');
        $this->activityLogger->record(
            $departure,
            $departure->total_seats,
            'departure_created',
            null,
            $data['activity_note'] ?? 'Total seats: '.$departure->total_seats,
        );

        return $departure;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDeparture(Departure $departure, array $data): Departure
    {
        $before = [
            'registered' => $departure->registered_pax,
            'total' => $departure->total_seats,
        ];
        $payload = [];
        foreach (['package_id', 'departure_date', 'return_date', 'total_seats', 'price', 'airline', 'status', 'notes'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }
        $departure->update($payload);

        $fresh = $departure->fresh(['package']);
        $this->activityLogger->recordFromSnapshot($fresh, $before, 'departure_updated', null, $data['activity_note'] ?? "Total: {$before['total']}->{$fresh->total_seats}");

        return $fresh;
    }

    public function cancelDeparture(Departure $departure): Departure
    {
        $departure->update(['status' => 'cancelled']);

        return $departure->fresh();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listRegistrations(?string $search = null): array
    {
        $query = Registration::query()->with('departure.package')->latest();

        if (filled($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        return $query->limit(100)->get()->map(fn (Registration $r) => $this->registrationPayload($r))->all();
    }

    public function getRegistration(int $id): ?array
    {
        $registration = Registration::query()->with('departure.package')->find($id);

        return $registration ? $this->registrationPayload($registration) : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRegistration(array $data): Registration
    {
        return DB::transaction(function () use ($data): Registration {
            $departure = Departure::query()->lockForUpdate()->findOrFail((int) $data['departure_id']);
            $pax = max(1, (int) ($data['pax'] ?? 1));
            $needPartner = (bool) ($data['need_partner'] ?? false);

            if ($pax > $departure->available_seats) {
                throw new \RuntimeException('Only '.$departure->available_seats.' seats available.');
            }

            if ($needPartner && empty($data['partner_gender'])) {
                throw new \RuntimeException('partner_gender is required when need_partner is true.');
            }

            $registration = Registration::create([
                'departure_id' => $departure->id,
                'name' => trim((string) $data['name']),
                'phone' => $data['phone'] ?? null,
                'pax' => $pax,
                'need_partner' => $needPartner,
                'partner_gender' => $needPartner ? ($data['partner_gender'] ?? null) : null,
                'notes' => $data['notes'] ?? null,
            ]);

            $departure->loadMissing('package');
            $this->activityLogger->record(
                $departure,
                $pax,
                'registration_created',
                trim((string) $data['name']),
                $data['activity_note'] ?? $data['notes'] ?? null,
            );

            return $registration;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateRegistration(Registration $registration, array $data): Registration
    {
        return DB::transaction(function () use ($registration, $data): Registration {
            $departure = Departure::query()->lockForUpdate()->findOrFail($registration->departure_id);
            $pax = isset($data['pax']) ? max(1, (int) $data['pax']) : $registration->pax;
            $available = $departure->available_seats + $registration->pax;

            if ($pax > $available) {
                throw new \RuntimeException('Only '.$available.' seats available.');
            }

            $needPartner = array_key_exists('need_partner', $data)
                ? (bool) $data['need_partner']
                : $registration->need_partner;

            $partnerGender = $needPartner
                ? ($data['partner_gender'] ?? $registration->partner_gender)
                : null;

            if ($needPartner && ! in_array($partnerGender, ['male', 'female'], true)) {
                throw new \RuntimeException('partner_gender is required when need_partner is true.');
            }

            $oldPax = $registration->pax;

            $registration->update([
                'name' => isset($data['name']) ? trim((string) $data['name']) : $registration->name,
                'phone' => array_key_exists('phone', $data) ? $data['phone'] : $registration->phone,
                'pax' => $pax,
                'need_partner' => $needPartner,
                'partner_gender' => $partnerGender,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $registration->notes,
            ]);

            $departure->loadMissing('package');
            $this->activityLogger->record(
                $departure,
                $pax - $oldPax,
                'registration_updated',
                $registration->name,
                $data['activity_note'] ?? "Pax {$oldPax}->{$pax}",
            );

            return $registration->fresh();
        });
    }

    public function deleteRegistration(Registration $registration): void
    {
        $departure = $registration->departure()->with('package')->first();
        $pax = $registration->pax;
        $name = $registration->name;
        $registration->delete();

        if ($departure) {
            $this->activityLogger->record($departure, -$pax, 'registration_deleted', $name);
        }
    }

    /**
     * @return array{reply: string, action: string, data: mixed}
     */
    public function chat(string $message): array
    {
        $text = trim($message);
        $lower = Str::lower($text);

        // Destructive intents require an explicit "confirm <command>" prefix (two-step safety).
        $confirming = false;
        if (preg_match('/^confirm\s+(.+)/u', $lower, $cm)) {
            $confirming = true;
            $lower = trim($cm[1]);
        }

        $destructive = $this->parseDestructiveIntent($lower);

        if ($destructive['matched'] && ! $confirming) {
            return [
                'action' => 'confirm_required',
                'reply' => "Amaran: arahan ini akan ubah data sebenar. Taip semula dengan **confirm** di depan untuk teruskan:\n\nconfirm {$lower}",
                'data' => null,
            ];
        }

        if ($text === '' || preg_match('/\b(help|bantuan|apa kau boleh)\b/u', $lower)) {
            return [
                'action' => 'help',
                'reply' => "Saya chatbot SeatWeb. Contoh:\n"
                    ."- list package / list trip / list pax\n"
                    ."- cari TRANSJAVA\n"
                    ."- berapa seat TRANSJAVA\n"
                    ."- delete package 6 / padam pakej 6 (perlu confirm)\n"
                    ."- cancel trip 10 (perlu confirm)\n"
                    ."- padam pax 12 (perlu confirm)\n"
                    .'- overview',
                'data' => null,
            ];
        }

        if (preg_match('/\b(overview|ringkasan|dashboard|berapa data)\b/u', $lower)) {
            $data = $this->overview();

            return [
                'action' => 'overview',
                'reply' => "Ada {$data['packages']} package, {$data['departures']} trip, {$data['registrations']} registration, jumlah {$data['total_pax']} pax.",
                'data' => $data,
            ];
        }

        if (preg_match('/\b(list|senarai)\b.*\b(package|pakej)\b/u', $lower)
            || preg_match('/\b(package|pakej)\b.*\b(list|senarai)\b/u', $lower)) {
            $rows = $this->listPackages();
            $lines = collect($rows)->take(20)->map(fn ($p) => "#{$p['id']} {$p['name']} ({$p['destination']}) — {$p['status']}")->implode("\n");

            return [
                'action' => 'list_packages',
                'reply' => $rows === [] ? 'Tiada package.' : "Packages:\n".$lines,
                'data' => $rows,
            ];
        }

        if (preg_match('/\b(list|senarai)\b.*\b(trip|departure)\b/u', $lower)) {
            $rows = $this->listDepartures();
            $lines = collect($rows)->take(20)->map(function ($d) {
                return "#{$d['id']} {$d['package_name']} {$d['departure_date']} — {$d['registered_pax']}/{$d['total_seats']} pax";
            })->implode("\n");

            return [
                'action' => 'list_departures',
                'reply' => $rows === [] ? 'Tiada trip.' : "Trips:\n".$lines,
                'data' => $rows,
            ];
        }

        if (preg_match('/\b(list|senarai)\b.*\b(pax|customer|participant|registration)\b/u', $lower)) {
            $rows = $this->listRegistrations();
            $lines = collect($rows)->take(20)->map(fn ($r) => "#{$r['id']} {$r['name']} ({$r['pax']} pax) — {$r['package_name']}")->implode("\n");

            return [
                'action' => 'list_registrations',
                'reply' => $rows === [] ? 'Tiada registration.' : "Participants:\n".$lines,
                'data' => $rows,
            ];
        }

        if (preg_match('/\b(delete|padam|hapus|archive|arsib)\b.*\b(package|pakej)\s+(\d+)/u', $lower, $m)
            || preg_match('/\b(package|pakej)\s+(\d+).*\b(delete|padam|hapus|archive|arsib)\b/u', $lower, $m2)) {
            $id = (int) ($m[3] ?? $m2[2] ?? 0);
            $package = Package::find($id);
            if (! $package) {
                return ['action' => 'error', 'reply' => "Package #{$id} tak jumpa.", 'data' => null];
            }
            $name = $package->name;
            $payload = $this->deletePackage($package);

            return [
                'action' => 'delete_package',
                'reply' => "Package #{$id} {$name} dipadam dari database. Trip + pax linked ikut hilang.",
                'data' => $payload,
            ];
        }

        if (preg_match('/\b(cancel|batal)\b.*\b(trip|departure)\s+(\d+)/u', $lower, $m)
            || preg_match('/\b(trip|departure)\s+(\d+).*\b(cancel|batal)\b/u', $lower, $m2)) {
            $id = (int) ($m[3] ?? $m2[2] ?? 0);
            $departure = Departure::find($id);
            if (! $departure) {
                return ['action' => 'error', 'reply' => "Trip #{$id} tak jumpa.", 'data' => null];
            }
            $this->cancelDeparture($departure);

            return ['action' => 'cancel_departure', 'reply' => "Trip #{$id} dibatalkan.", 'data' => $this->departurePayload($departure->fresh())];
        }

        if (preg_match('/\b(padam|delete|hapus)\b.*\b(pax|registration|customer)\s+(\d+)/u', $lower, $m)
            || preg_match('/\b(pax|registration)\s+(\d+).*\b(padam|delete|hapus)\b/u', $lower, $m2)) {
            $id = (int) ($m[3] ?? $m2[2] ?? 0);
            $registration = Registration::find($id);
            if (! $registration) {
                return ['action' => 'error', 'reply' => "Pax #{$id} tak jumpa.", 'data' => null];
            }
            $name = $registration->name;
            $this->deleteRegistration($registration);

            return ['action' => 'delete_registration', 'reply' => "Pax #{$id} {$name} dipadam.", 'data' => ['id' => $id]];
        }

        if (preg_match('/\b(cari|search|show|tengok|nampak)\b\s+(.+)/u', $lower, $m)
            || preg_match('/\b(package|pakej|trip)\b\s+([a-z0-9][a-z0-9 \-]{1,60})$/u', $lower, $m2)) {
            $term = trim($m[2] ?? $m2[2] ?? '');
            $term = preg_replace('/\b(package|pakej|trip|pax)\b/u', '', $term) ?? $term;
            $term = trim($term);
            if ($term === '') {
                return ['action' => 'help', 'reply' => 'Cuba: cari TRANSJAVA', 'data' => null];
            }

            $packages = $this->listPackages($term);
            $pax = $this->listRegistrations($term);
            $trips = $this->listDepartures($term);

            $bits = [];
            if ($packages !== []) {
                $bits[] = 'Package: '.collect($packages)->map(fn ($p) => "#{$p['id']} {$p['name']}")->implode(', ');
            }
            if ($trips !== []) {
                $bits[] = 'Trip: '.collect($trips)->take(8)->map(fn ($d) => "#{$d['id']} {$d['package_name']} {$d['departure_date']}")->implode(', ');
            }
            if ($pax !== []) {
                $bits[] = 'Pax: '.collect($pax)->take(8)->map(fn ($r) => "#{$r['id']} {$r['name']}")->implode(', ');
            }

            return [
                'action' => 'search',
                'reply' => $bits === [] ? "Tak jumpa \"{$term}\"." : implode("\n", $bits),
                'data' => compact('packages', 'trips', 'pax'),
            ];
        }

        if (preg_match('/\b(seat|kapasiti)\b/u', $lower)) {
            $term = null;
            if (preg_match('/\b(seat|kapasiti)\b\s+(.+)/u', $lower, $m)) {
                $term = trim($m[2]);
            }
            $trips = $this->listDepartures($term);
            $lines = collect($trips)->take(15)->map(function ($d) {
                $avail = $d['available_seats'];

                return "#{$d['id']} {$d['package_name']} {$d['departure_date']}: {$avail} tinggal / {$d['total_seats']}";
            })->implode("\n");

            return [
                'action' => 'seats',
                'reply' => $trips === [] ? 'Tiada trip.' : $lines,
                'data' => $trips,
            ];
        }

        return [
            'action' => 'unknown',
            'reply' => 'Tak faham. Taip **help**. Contoh: list package, cari TRANSJAVA, padam pax 12.',
            'data' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function packagePayload(Package $package, bool $withDepartures = false): array
    {
        $payload = [
            'id' => $package->id,
            'name' => $package->name,
            'destination' => $package->destination,
            'description' => $package->description,
            'status' => $package->status,
            'departures_count' => $package->departures_count ?? $package->departures()->count(),
        ];

        if ($withDepartures) {
            $payload['departures'] = $package->departures->map(fn (Departure $d) => $this->departurePayload($d))->all();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function departurePayload(Departure $departure, bool $withRegistrations = false): array
    {
        $departure->loadMissing('package', 'registrations');

        $payload = [
            'id' => $departure->id,
            'package_id' => $departure->package_id,
            'package_name' => $departure->package?->name,
            'destination' => $departure->package?->destination,
            'departure_date' => $departure->departure_date?->toDateString(),
            'return_date' => $departure->return_date?->toDateString(),
            'total_seats' => $departure->total_seats,
            'registered_pax' => $departure->registered_pax,
            'available_seats' => $departure->available_seats,
            'price' => $departure->price,
            'airline' => $departure->airline,
            'status' => $departure->status,
            'status_label' => $departure->status_label,
            'notes' => $departure->notes,
        ];

        if ($withRegistrations) {
            $payload['registrations'] = $departure->registrations->map(fn (Registration $r) => $this->registrationPayload($r))->all();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function registrationPayload(Registration $registration): array
    {
        $registration->loadMissing('departure.package');

        return [
            'id' => $registration->id,
            'departure_id' => $registration->departure_id,
            'package_name' => $registration->departure?->package?->name,
            'departure_date' => $registration->departure?->departure_date?->toDateString(),
            'name' => $registration->name,
            'phone' => $registration->phone,
            'pax' => $registration->pax,
            'need_partner' => $registration->need_partner,
            'partner_gender' => $registration->partner_gender,
            'notes' => $registration->notes,
        ];
    }
}
