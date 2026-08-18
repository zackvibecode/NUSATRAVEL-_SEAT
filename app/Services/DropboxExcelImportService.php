<?php

namespace App\Services;

use App\Models\Departure;
use App\Models\ImportRun;
use App\Models\Package;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class DropboxExcelImportService
{
    public function __construct(private HermesSeatActivityLogger $activityLogger) {}

    /**
     * Upsert packages, departures, and registrations from a structured payload.
     *
     * @param  array{
     *     filename?: string|null,
     *     dropbox_path?: string|null,
     *     packages?: list<array<string, mixed>>,
     *     departures?: list<array<string, mixed>>,
     *     registrations?: list<array<string, mixed>>,
     * }  $payload
     * @return array{import_run_id: int|null, dry_run: bool, status: string, counts: array<string, int>, errors: list<array<string, mixed>>, summary: array<string, mixed>}
     */
    public function import(array $payload, bool $dryRun = false): array
    {
        if ($dryRun) {
            return $this->importDryRunOnly($payload);
        }

        $counts = [
            'packages_created' => 0,
            'packages_updated' => 0,
            'departures_created' => 0,
            'departures_updated' => 0,
            'registrations_created' => 0,
            'registrations_updated' => 0,
            'skipped' => 0,
        ];
        $errors = [];
        $summary = [
            'packages' => [],
            'departures' => [],
            'registrations' => [],
        ];

        /** @var array<string, Package> */
        $packageIndex = [];
        /** @var array<string, Departure> */
        $departureIndex = [];

        $availableBefore = $this->activityLogger->snapshotAvailable(
            $this->findDeparturesTouchedByPayload($payload),
        );

        try {
            DB::transaction(function () use ($payload, &$counts, &$errors, &$summary, &$packageIndex, &$departureIndex) {
                foreach ($payload['packages'] ?? [] as $index => $row) {
                    $this->upsertPackage($row, $index, false, $counts, $errors, $summary, $packageIndex);
                }

                foreach ($payload['departures'] ?? [] as $index => $row) {
                    $this->upsertDeparture($row, $index, false, $counts, $errors, $summary, $packageIndex, $departureIndex);
                }

                foreach ($payload['registrations'] ?? [] as $index => $row) {
                    $this->upsertRegistration($row, $index, false, $counts, $errors, $summary, $packageIndex, $departureIndex);
                }
            });
        } catch (Throwable $e) {
            $errors[] = [
                'entity' => 'import',
                'index' => null,
                'message' => $e->getMessage(),
            ];

            $run = ImportRun::create([
                'source' => 'dropbox-excel',
                'filename' => $payload['filename'] ?? null,
                'dropbox_path' => $payload['dropbox_path'] ?? null,
                'dry_run' => false,
                'status' => 'failed',
                ...$counts,
                'errors' => $errors,
                'summary' => $summary,
            ]);

            return [
                'import_run_id' => $run->id,
                'dry_run' => false,
                'status' => 'failed',
                'counts' => $counts,
                'errors' => $errors,
                'summary' => $summary,
            ];
        }

        $status = count($errors) > 0 ? 'completed_with_errors' : 'completed';

        foreach ($this->findDeparturesTouchedByPayload($payload) as $departure) {
            $before = $availableBefore[$departure->id] ?? 0;
            $this->activityLogger->record($departure, $departure->available_seats - $before);
        }

        $run = ImportRun::create([
            'source' => 'dropbox-excel',
            'filename' => $payload['filename'] ?? null,
            'dropbox_path' => $payload['dropbox_path'] ?? null,
            'dry_run' => false,
            'status' => $status,
            ...$counts,
            'errors' => $errors,
            'summary' => $summary,
        ]);

        return [
            'import_run_id' => $run->id,
            'dry_run' => false,
            'status' => $status,
            'counts' => $counts,
            'errors' => $errors,
            'summary' => $summary,
        ];
    }

    /**
     * Pure dry-run: no DB writes except import_runs log.
     *
     * @param  array<string, mixed>  $payload
     * @return array{import_run_id: int|null, dry_run: bool, status: string, counts: array<string, int>, errors: list<array<string, mixed>>, summary: array<string, mixed>}
     */
    private function importDryRunOnly(array $payload): array
    {
        $counts = [
            'packages_created' => 0,
            'packages_updated' => 0,
            'departures_created' => 0,
            'departures_updated' => 0,
            'registrations_created' => 0,
            'registrations_updated' => 0,
            'skipped' => 0,
        ];
        $errors = [];
        $summary = [
            'packages' => [],
            'departures' => [],
            'registrations' => [],
        ];

        /** @var array<string, Package|array<string, mixed>> */
        $packageIndex = [];
        /** @var array<string, Departure|array<string, mixed>> */
        $departureIndex = [];
        /** @var array<string, int> simulated pax per departure key */
        $simulatedPax = [];

        foreach ($payload['packages'] ?? [] as $index => $row) {
            $this->previewPackage($row, $index, $counts, $errors, $summary, $packageIndex);
        }

        foreach ($payload['departures'] ?? [] as $index => $row) {
            $this->previewDeparture($row, $index, $counts, $errors, $summary, $packageIndex, $departureIndex, $simulatedPax);
        }

        foreach ($payload['registrations'] ?? [] as $index => $row) {
            $this->previewRegistration($row, $index, $counts, $errors, $summary, $packageIndex, $departureIndex, $simulatedPax);
        }

        $status = count($errors) > 0 ? 'dry_run_with_errors' : 'dry_run';

        $run = ImportRun::create([
            'source' => 'dropbox-excel',
            'filename' => $payload['filename'] ?? null,
            'dropbox_path' => $payload['dropbox_path'] ?? null,
            'dry_run' => true,
            'status' => $status,
            ...$counts,
            'errors' => $errors,
            'summary' => $summary,
        ]);

        return [
            'import_run_id' => $run->id,
            'dry_run' => true,
            'status' => $status,
            'counts' => $counts,
            'errors' => $errors,
            'summary' => $summary,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, int>  $counts
     * @param  list<array<string, mixed>>  $errors
     * @param  array<string, mixed>  $summary
     * @param  array<string, Package>  $packageIndex
     */
    private function upsertPackage(array $row, int $index, bool $dryRun, array &$counts, array &$errors, array &$summary, array &$packageIndex): void
    {
        $name = trim((string) ($row['name'] ?? ''));
        $destination = trim((string) ($row['destination'] ?? ''));

        if ($name === '' || $destination === '') {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'package',
                'index' => $index,
                'message' => 'name and destination are required.',
                'row' => $row,
            ];

            return;
        }

        $status = strtolower(trim((string) ($row['status'] ?? 'active')));
        if (! in_array($status, ['active', 'archived'], true)) {
            $status = 'active';
        }

        $key = $this->packageKey($name, $destination);
        $package = Package::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->whereRaw('LOWER(destination) = ?', [Str::lower($destination)])
            ->first();

        $attributes = [
            'name' => $name,
            'destination' => $destination,
            'description' => $row['description'] ?? null,
            'status' => $status,
        ];

        if ($package) {
            $package->fill($attributes);
            if (! $dryRun) {
                $package->save();
            }
            $counts['packages_updated']++;
            $action = 'updated';
        } else {
            $package = $dryRun
                ? new Package($attributes)
                : Package::create($attributes);
            $counts['packages_created']++;
            $action = 'created';
        }

        $packageIndex[$key] = $package;
        $summary['packages'][] = [
            'action' => $action,
            'name' => $name,
            'destination' => $destination,
            'id' => $package->id,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, int>  $counts
     * @param  list<array<string, mixed>>  $errors
     * @param  array<string, mixed>  $summary
     * @param  array<string, Package>  $packageIndex
     * @param  array<string, Departure>  $departureIndex
     */
    private function upsertDeparture(
        array $row,
        int $index,
        bool $dryRun,
        array &$counts,
        array &$errors,
        array &$summary,
        array &$packageIndex,
        array &$departureIndex,
    ): void {
        $packageName = trim((string) ($row['package_name'] ?? $row['package'] ?? ''));
        $destination = trim((string) ($row['destination'] ?? ''));
        $departureDateRaw = $row['departure_date'] ?? null;
        $returnDateRaw = $row['return_date'] ?? null;

        if ($packageName === '' || ! $departureDateRaw || ! $returnDateRaw) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'departure',
                'index' => $index,
                'message' => 'package_name (or package), departure_date, and return_date are required.',
                'row' => $row,
            ];

            return;
        }

        try {
            $departureDate = Carbon::parse($departureDateRaw)->toDateString();
            $returnDate = Carbon::parse($returnDateRaw)->toDateString();
        } catch (Throwable) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'departure',
                'index' => $index,
                'message' => 'Invalid departure_date or return_date.',
                'row' => $row,
            ];

            return;
        }

        if ($returnDate < $departureDate) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'departure',
                'index' => $index,
                'message' => 'return_date must be on or after departure_date.',
                'row' => $row,
            ];

            return;
        }

        $package = $this->resolvePackage($packageName, $destination, $packageIndex);
        if (! $package || ! $package->id) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'departure',
                'index' => $index,
                'message' => "Package not found: {$packageName}".($destination !== '' ? " / {$destination}" : ''),
                'row' => $row,
            ];

            return;
        }

        $totalSeats = max(1, (int) ($row['total_seats'] ?? 1));
        $status = isset($row['status']) ? strtolower(trim((string) $row['status'])) : 'open';
        if (! in_array($status, ['open', 'cancelled'], true)) {
            $status = 'open';
        }

        $attributes = [
            'package_id' => $package->id,
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'total_seats' => $totalSeats,
            'price' => $row['price'] ?? null,
            'airline' => $row['airline'] ?? null,
            'status' => $status,
            'notes' => $row['notes'] ?? null,
        ];

        $departure = Departure::query()
            ->where('package_id', $package->id)
            ->whereDate('departure_date', $departureDate)
            ->first();

        if ($departure) {
            $departure->fill($attributes);
            if (! $dryRun) {
                $departure->save();
            }
            $counts['departures_updated']++;
            $action = 'updated';
        } else {
            $departure = $dryRun
                ? new Departure($attributes)
                : Departure::create($attributes);
            $counts['departures_created']++;
            $action = 'created';
        }

        $dKey = $this->departureKey($package->id, $departureDate);
        $departureIndex[$dKey] = $departure;
        // Also index by package name + date for registration rows that omit destination
        $departureIndex[$this->departureKeyByName($packageName, $departureDate)] = $departure;

        $summary['departures'][] = [
            'action' => $action,
            'package' => $packageName,
            'departure_date' => $departureDate,
            'id' => $departure->id,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, int>  $counts
     * @param  list<array<string, mixed>>  $errors
     * @param  array<string, mixed>  $summary
     * @param  array<string, Package>  $packageIndex
     * @param  array<string, Departure>  $departureIndex
     */
    private function upsertRegistration(
        array $row,
        int $index,
        bool $dryRun,
        array &$counts,
        array &$errors,
        array &$summary,
        array &$packageIndex,
        array &$departureIndex,
    ): void {
        $name = trim((string) ($row['name'] ?? $row['customer_name'] ?? ''));
        $phone = isset($row['phone']) ? trim((string) $row['phone']) : null;
        $pax = max(1, (int) ($row['pax'] ?? 1));
        $needPartner = $this->toBool($row['need_partner'] ?? false);
        $partnerGender = isset($row['partner_gender']) ? strtolower(trim((string) $row['partner_gender'])) : null;

        $packageName = trim((string) ($row['package_name'] ?? $row['package'] ?? ''));
        $destination = trim((string) ($row['destination'] ?? ''));
        $departureDateRaw = $row['departure_date'] ?? null;

        if ($name === '' || ! $departureDateRaw) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'registration',
                'index' => $index,
                'message' => 'name and departure_date are required.',
                'row' => $row,
            ];

            return;
        }

        if ($needPartner && ! in_array($partnerGender, ['male', 'female'], true)) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'registration',
                'index' => $index,
                'message' => 'partner_gender (male|female) is required when need_partner is true.',
                'row' => $row,
            ];

            return;
        }

        if (! $needPartner) {
            $partnerGender = null;
        }

        try {
            $departureDate = Carbon::parse($departureDateRaw)->toDateString();
        } catch (Throwable) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'registration',
                'index' => $index,
                'message' => 'Invalid departure_date.',
                'row' => $row,
            ];

            return;
        }

        $departure = $this->resolveDeparture($row, $packageName, $destination, $departureDate, $packageIndex, $departureIndex);
        if (! $departure || ! $departure->id) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'registration',
                'index' => $index,
                'message' => 'Departure not found for registration.',
                'row' => $row,
            ];

            return;
        }

        $phoneNorm = $this->normalizePhone($phone);
        $existing = Registration::query()
            ->where('departure_id', $departure->id)
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->get()
            ->first(function (Registration $reg) use ($phoneNorm) {
                return $this->normalizePhone($reg->phone) === $phoneNorm;
            });

        $available = $departure->available_seats + ($existing?->pax ?? 0);
        if ($pax > $available) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'registration',
                'index' => $index,
                'message' => "Only {$available} seats available for this departure.",
                'row' => $row,
            ];

            return;
        }

        $attributes = [
            'departure_id' => $departure->id,
            'name' => $name,
            'phone' => $phone,
            'pax' => $pax,
            'need_partner' => $needPartner,
            'partner_gender' => $partnerGender,
            'notes' => $row['notes'] ?? null,
            'invoice_no' => $this->nullIfBlank($row, 'invoice_no'),
            'pic_utama' => $this->nullIfBlank($row, 'pic_utama'),
            'pic_in_house' => $this->nullIfBlank($row, 'pic_in_house'),
            'invoice_status' => $this->nullIfBlank($row, 'invoice_status'),
            'invoice_amount' => isset($row['invoice_amount']) && $row['invoice_amount'] !== '' && is_numeric($row['invoice_amount'])
                ? (float) $row['invoice_amount']
                : ($existing?->invoice_amount ?? null),
            'total_paid' => isset($row['total_paid']) && $row['total_paid'] !== '' && is_numeric($row['total_paid'])
                ? (float) $row['total_paid']
                : ($existing?->total_paid ?? null),
            'invoice_url' => $this->nullIfBlank($row, 'invoice_url'),
        ];

        if ($existing) {
            $existing->fill($attributes);
            if (! $dryRun) {
                $existing->save();
            }
            $counts['registrations_updated']++;
            $action = 'updated';
            $regId = $existing->id;
        } else {
            $created = $dryRun
                ? new Registration($attributes)
                : Registration::create($attributes);
            $counts['registrations_created']++;
            $action = 'created';
            $regId = $created->id;
        }

        $summary['registrations'][] = [
            'action' => $action,
            'name' => $name,
            'departure_id' => $departure->id,
            'id' => $regId,
        ];
    }

    // --- Dry-run preview helpers (read DB, no writes to domain tables) ---

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, int>  $counts
     * @param  list<array<string, mixed>>  $errors
     * @param  array<string, mixed>  $summary
     * @param  array<string, Package|array<string, mixed>>  $packageIndex
     */
    private function previewPackage(array $row, int $index, array &$counts, array &$errors, array &$summary, array &$packageIndex): void
    {
        $name = trim((string) ($row['name'] ?? ''));
        $destination = trim((string) ($row['destination'] ?? ''));

        if ($name === '' || $destination === '') {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'package',
                'index' => $index,
                'message' => 'name and destination are required.',
                'row' => $row,
            ];

            return;
        }

        $key = $this->packageKey($name, $destination);
        $existing = Package::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->whereRaw('LOWER(destination) = ?', [Str::lower($destination)])
            ->first();

        if ($existing) {
            $counts['packages_updated']++;
            $packageIndex[$key] = $existing;
            $action = 'updated';
            $id = $existing->id;
        } else {
            $counts['packages_created']++;
            $packageIndex[$key] = [
                'id' => null,
                'name' => $name,
                'destination' => $destination,
            ];
            $action = 'created';
            $id = null;
        }

        $summary['packages'][] = [
            'action' => $action,
            'name' => $name,
            'destination' => $destination,
            'id' => $id,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, int>  $counts
     * @param  list<array<string, mixed>>  $errors
     * @param  array<string, mixed>  $summary
     * @param  array<string, Package|array<string, mixed>>  $packageIndex
     * @param  array<string, Departure|array<string, mixed>>  $departureIndex
     * @param  array<string, int>  $simulatedPax
     */
    private function previewDeparture(
        array $row,
        int $index,
        array &$counts,
        array &$errors,
        array &$summary,
        array &$packageIndex,
        array &$departureIndex,
        array &$simulatedPax,
    ): void {
        $packageName = trim((string) ($row['package_name'] ?? $row['package'] ?? ''));
        $destination = trim((string) ($row['destination'] ?? ''));
        $departureDateRaw = $row['departure_date'] ?? null;
        $returnDateRaw = $row['return_date'] ?? null;

        if ($packageName === '' || ! $departureDateRaw || ! $returnDateRaw) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'departure',
                'index' => $index,
                'message' => 'package_name (or package), departure_date, and return_date are required.',
                'row' => $row,
            ];

            return;
        }

        try {
            $departureDate = Carbon::parse($departureDateRaw)->toDateString();
            $returnDate = Carbon::parse($returnDateRaw)->toDateString();
        } catch (Throwable) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'departure',
                'index' => $index,
                'message' => 'Invalid departure_date or return_date.',
                'row' => $row,
            ];

            return;
        }

        $package = $this->resolvePackage($packageName, $destination, $packageIndex);
        $packageId = is_object($package) ? $package->id : ($package['id'] ?? null);

        if (! $packageId && ! is_array($package)) {
            // New package in same dry-run payload without id yet — still OK if present in index
            if (! is_array($package) && ! $package) {
                $counts['skipped']++;
                $errors[] = [
                    'entity' => 'departure',
                    'index' => $index,
                    'message' => "Package not found: {$packageName}",
                    'row' => $row,
                ];

                return;
            }
        }

        $totalSeats = max(1, (int) ($row['total_seats'] ?? 1));
        $existing = $packageId
            ? Departure::query()
                ->where('package_id', $packageId)
                ->whereDate('departure_date', $departureDate)
                ->first()
            : null;

        if ($existing) {
            $counts['departures_updated']++;
            $action = 'updated';
            $id = $existing->id;
            $departureIndex[$this->departureKey($packageId, $departureDate)] = $existing;
            $departureIndex[$this->departureKeyByName($packageName, $departureDate)] = $existing;
            $simulatedPax[$this->departureKeyByName($packageName, $departureDate)] = (int) $existing->registrations()->sum('pax');
            $simulatedPax['seats:'.$this->departureKeyByName($packageName, $departureDate)] = (int) ($row['total_seats'] ?? $existing->total_seats);
        } else {
            $counts['departures_created']++;
            $action = 'created';
            $id = null;
            $virtual = [
                'id' => null,
                'package_id' => $packageId,
                'departure_date' => $departureDate,
                'total_seats' => $totalSeats,
            ];
            if ($packageId) {
                $departureIndex[$this->departureKey($packageId, $departureDate)] = $virtual;
            }
            $departureIndex[$this->departureKeyByName($packageName, $departureDate)] = $virtual;
            $simulatedPax[$this->departureKeyByName($packageName, $departureDate)] = 0;
            $simulatedPax['seats:'.$this->departureKeyByName($packageName, $departureDate)] = $totalSeats;
        }

        $summary['departures'][] = [
            'action' => $action,
            'package' => $packageName,
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'id' => $id,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, int>  $counts
     * @param  list<array<string, mixed>>  $errors
     * @param  array<string, mixed>  $summary
     * @param  array<string, Package|array<string, mixed>>  $packageIndex
     * @param  array<string, Departure|array<string, mixed>>  $departureIndex
     * @param  array<string, int>  $simulatedPax
     */
    private function previewRegistration(
        array $row,
        int $index,
        array &$counts,
        array &$errors,
        array &$summary,
        array &$packageIndex,
        array &$departureIndex,
        array &$simulatedPax,
    ): void {
        $name = trim((string) ($row['name'] ?? $row['customer_name'] ?? ''));
        $phone = isset($row['phone']) ? trim((string) $row['phone']) : null;
        $pax = max(1, (int) ($row['pax'] ?? 1));
        $needPartner = $this->toBool($row['need_partner'] ?? false);
        $partnerGender = isset($row['partner_gender']) ? strtolower(trim((string) $row['partner_gender'])) : null;
        $packageName = trim((string) ($row['package_name'] ?? $row['package'] ?? ''));
        $destination = trim((string) ($row['destination'] ?? ''));
        $departureDateRaw = $row['departure_date'] ?? null;

        if ($name === '' || ! $departureDateRaw) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'registration',
                'index' => $index,
                'message' => 'name and departure_date are required.',
                'row' => $row,
            ];

            return;
        }

        if ($needPartner && ! in_array($partnerGender, ['male', 'female'], true)) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'registration',
                'index' => $index,
                'message' => 'partner_gender (male|female) is required when need_partner is true.',
                'row' => $row,
            ];

            return;
        }

        try {
            $departureDate = Carbon::parse($departureDateRaw)->toDateString();
        } catch (Throwable) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'registration',
                'index' => $index,
                'message' => 'Invalid departure_date.',
                'row' => $row,
            ];

            return;
        }

        $nameKey = $this->departureKeyByName($packageName !== '' ? $packageName : 'unknown', $departureDate);
        $departure = $this->resolveDeparture($row, $packageName, $destination, $departureDate, $packageIndex, $departureIndex);

        $departureId = is_object($departure) ? $departure->id : ($departure['id'] ?? null);
        $seatsKey = 'seats:'.$nameKey;
        $totalSeats = $simulatedPax[$seatsKey]
            ?? (is_object($departure) ? $departure->total_seats : ($departure['total_seats'] ?? null));

        if ($totalSeats === null && ! $departure) {
            $counts['skipped']++;
            $errors[] = [
                'entity' => 'registration',
                'index' => $index,
                'message' => 'Departure not found for registration.',
                'row' => $row,
            ];

            return;
        }

        $used = $simulatedPax[$nameKey] ?? 0;
        if ($departureId) {
            $existing = Registration::query()
                ->where('departure_id', $departureId)
                ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
                ->get()
                ->first(fn (Registration $reg) => $this->normalizePhone($reg->phone) === $this->normalizePhone($phone));

            $available = (is_object($departure) ? $departure->available_seats : ((int) $totalSeats - $used)) + ($existing?->pax ?? 0);

            if ($pax > $available) {
                $counts['skipped']++;
                $errors[] = [
                    'entity' => 'registration',
                    'index' => $index,
                    'message' => "Only {$available} seats available for this departure.",
                    'row' => $row,
                ];

                return;
            }

            if ($existing) {
                $simulatedPax[$nameKey] = $used - $existing->pax + $pax;
                $counts['registrations_updated']++;
                $action = 'updated';
                $id = $existing->id;
            } else {
                $simulatedPax[$nameKey] = $used + $pax;
                $counts['registrations_created']++;
                $action = 'created';
                $id = null;
            }
        } else {
            $available = (int) $totalSeats - $used;
            if ($pax > $available) {
                $counts['skipped']++;
                $errors[] = [
                    'entity' => 'registration',
                    'index' => $index,
                    'message' => "Only {$available} seats available for this departure.",
                    'row' => $row,
                ];

                return;
            }
            $simulatedPax[$nameKey] = $used + $pax;
            $counts['registrations_created']++;
            $action = 'created';
            $id = null;
        }

        $summary['registrations'][] = [
            'action' => $action,
            'name' => $name,
            'departure_id' => $departureId,
            'id' => $id,
        ];
    }

    /**
     * Existing + just-imported trips mentioned in this payload, with current available seats.
     *
     * @param  array<string, mixed>  $payload
     * @return Collection<int, Departure>
     */
    private function findDeparturesTouchedByPayload(array $payload): Collection
    {
        $ids = [];
        $packageIndex = [];
        $departureIndex = [];

        foreach (array_merge($payload['departures'] ?? [], $payload['registrations'] ?? []) as $row) {
            $packageName = trim((string) ($row['package_name'] ?? $row['package'] ?? ''));
            $destination = trim((string) ($row['destination'] ?? ''));
            $dateRaw = $row['departure_date'] ?? null;

            if ($dateRaw === null && empty($row['departure_id'])) {
                continue;
            }

            $date = '';
            if ($dateRaw) {
                try {
                    $date = Carbon::parse($dateRaw)->toDateString();
                } catch (Throwable) {
                    continue;
                }
            }

            $found = $this->resolveDeparture(
                $row,
                $packageName,
                $destination,
                $date,
                $packageIndex,
                $departureIndex,
            );

            if ($found instanceof Departure && $found->id) {
                $ids[] = $found->id;
            }
        }

        if ($ids === []) {
            return collect();
        }

        return Departure::query()
            ->with('package')
            ->withSum('registrations as registered_pax_sum', 'pax')
            ->whereIn('id', array_values(array_unique($ids)))
            ->get();
    }

    /**
     * @param  array<string, Package|array<string, mixed>>  $packageIndex
     */
    private function resolvePackage(string $packageName, string $destination, array &$packageIndex): Package|array|null
    {
        if ($destination !== '') {
            $key = $this->packageKey($packageName, $destination);
            if (isset($packageIndex[$key])) {
                return $packageIndex[$key];
            }
        }

        foreach ($packageIndex as $pkg) {
            $n = is_object($pkg) ? $pkg->name : ($pkg['name'] ?? '');
            if (Str::lower($n) === Str::lower($packageName)) {
                if ($destination === '') {
                    return $pkg;
                }
                $d = is_object($pkg) ? $pkg->destination : ($pkg['destination'] ?? '');
                if (Str::lower($d) === Str::lower($destination)) {
                    return $pkg;
                }
            }
        }

        $query = Package::query()->whereRaw('LOWER(name) = ?', [Str::lower($packageName)]);
        if ($destination !== '') {
            $query->whereRaw('LOWER(destination) = ?', [Str::lower($destination)]);
        }
        $found = $query->first();
        if ($found) {
            $packageIndex[$this->packageKey($found->name, $found->destination)] = $found;
        }

        return $found;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, Package|array<string, mixed>>  $packageIndex
     * @param  array<string, Departure|array<string, mixed>>  $departureIndex
     */
    private function resolveDeparture(
        array $row,
        string $packageName,
        string $destination,
        string $departureDate,
        array &$packageIndex,
        array &$departureIndex,
    ): Departure|array|null {
        if (isset($row['departure_id'])) {
            $byId = Departure::find((int) $row['departure_id']);
            if ($byId) {
                return $byId;
            }
        }

        if ($packageName !== '') {
            $byName = $departureIndex[$this->departureKeyByName($packageName, $departureDate)] ?? null;
            if ($byName) {
                return $byName;
            }
        }

        $package = $packageName !== ''
            ? $this->resolvePackage($packageName, $destination, $packageIndex)
            : null;

        $packageId = is_object($package) ? $package->id : ($package['id'] ?? null);
        if ($packageId) {
            $key = $this->departureKey($packageId, $departureDate);
            if (isset($departureIndex[$key])) {
                return $departureIndex[$key];
            }

            $found = Departure::query()
                ->where('package_id', $packageId)
                ->whereDate('departure_date', $departureDate)
                ->first();
            if ($found) {
                $departureIndex[$key] = $found;
                if ($packageName !== '') {
                    $departureIndex[$this->departureKeyByName($packageName, $departureDate)] = $found;
                }
            }

            return $found;
        }

        return null;
    }

    private function packageKey(string $name, string $destination): string
    {
        return Str::lower(trim($name)).'|'.Str::lower(trim($destination));
    }

    private function departureKey(int|string $packageId, string $departureDate): string
    {
        return $packageId.'|'.$departureDate;
    }

    private function departureKeyByName(string $packageName, string $departureDate): string
    {
        return 'name:'.Str::lower(trim($packageName)).'|'.$departureDate;
    }

    private function normalizePhone(?string $phone): string
    {
        if ($phone === null || trim($phone) === '') {
            return '';
        }

        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    /**
     * Blank-ish strings ('', '  ', 'null', '-') become real nulls so the
     * derived payment logic never treats placeholder text as data.
     */
    private function nullIfBlank(array $row, string $key): ?string
    {
        $value = trim((string) ($row[$key] ?? ''));

        return $value === '' || strtolower($value) === 'null' ? null : $value;
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = Str::lower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'y', 'on'], true);
    }
}
