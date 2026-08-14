<?php

namespace App\Support;

use App\Models\Departure;
use App\Models\Package;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TripListFilter
{
    /** @var array<int, string> */
    public const MONTHS = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];

    public ?int $month = null;

    public ?int $year = null;

    public ?string $destination = null;

    public ?int $packageId = null;

    public ?string $status = null;

    /**
     * Free-text search across package name, destination, airline and
     * registered customer/participant name (PRD section 15).
     */
    public ?string $search = null;

    public string $sort;

    public string $dir;

    public function __construct(
        public string $context,
        public string $actionRoute,
        array $params = [],
    ) {
        $this->month = isset($params['month']) && $params['month'] !== '' ? (int) $params['month'] : null;
        $this->year = isset($params['year']) && $params['year'] !== '' ? (int) $params['year'] : null;
        $this->destination = filled($params['destination'] ?? null) ? (string) $params['destination'] : null;
        $this->packageId = isset($params['package_id']) && $params['package_id'] !== '' ? (int) $params['package_id'] : null;
        $this->status = filled($params['status'] ?? null) ? (string) $params['status'] : null;
        $this->search = filled($params['search'] ?? null) ? trim((string) $params['search']) : null;

        $defaults = self::defaultSort($context);
        $this->sort = self::allowedSort($context, $params['sort'] ?? null) ?? $defaults['sort'];
        $this->dir = in_array($params['dir'] ?? null, ['asc', 'desc'], true)
            ? $params['dir']
            : $defaults['dir'];
    }

    public static function fromRequest(Request $request, string $context, string $actionRoute): self
    {
        return new self($context, $actionRoute, $request->query());
    }

    /**
     * @return array{sort: string, dir: string}
     */
    public static function defaultSort(string $context): array
    {
        return match ($context) {
            'departures' => ['sort' => 'departure_date', 'dir' => 'desc'],
            'packages' => ['sort' => 'name', 'dir' => 'asc'],
            default => ['sort' => 'departure_date', 'dir' => 'asc'],
        };
    }

    public static function allowedSort(string $context, ?string $sort): ?string
    {
        $allowed = match ($context) {
            'packages' => ['name', 'destination', 'departures_count'],
            'participants', 'need_partner' => ['departure_date', 'name', 'package_name'],
            default => ['departure_date', 'package_name', 'destination'],
        };

        return in_array($sort, $allowed, true) ? $sort : null;
    }

    public function isActive(): bool
    {
        return $this->month !== null
            || $this->year !== null
            || $this->destination !== null
            || $this->packageId !== null
            || $this->search !== null
            || ($this->status !== null && $this->context === 'packages');
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public function queryParams(array $extra = []): array
    {
        $params = array_filter([
            'month' => $this->month,
            'year' => $this->year,
            'destination' => $this->destination,
            'package_id' => $this->packageId,
            'status' => $this->status,
            'search' => $this->search,
            'sort' => $this->sort,
            'dir' => $this->dir,
        ], fn ($v) => $v !== null && $v !== '');

        return array_merge($params, array_filter($extra, fn ($v) => $v !== null && $v !== ''));
    }

    public function sortUrl(string $sort, ?string $dir = null, array $extra = []): string
    {
        $nextDir = $dir ?? ($this->sort === $sort && $this->dir === 'asc' ? 'desc' : 'asc');

        return route($this->actionRoute, $this->queryParams(array_merge($extra, [
            'sort' => $sort,
            'dir' => $nextDir,
        ])));
    }

    public function sortIndicator(string $column): string
    {
        if ($this->sort !== $column) {
            return '';
        }

        return $this->dir === 'asc' ? ' ↑' : ' ↓';
    }

    /**
     * @return Builder<Departure>
     */
    public function applyToDepartureQuery(Builder $query, bool $upcomingOnly = false): Builder
    {
        if ($upcomingOnly) {
            $query->upcoming();
        }

        if ($this->month !== null) {
            $query->whereMonth('departure_date', $this->month);
        }

        if ($this->year !== null) {
            $query->whereYear('departure_date', $this->year);
        }

        if ($this->packageId !== null) {
            $query->where('package_id', $this->packageId);
        }

        if ($this->destination !== null) {
            $query->whereHas('package', fn (Builder $q) => $q->where('destination', $this->destination));
        }

        if ($this->search !== null) {
            $term = "%{$this->search}%";

            $query->where(function (Builder $q) use ($term) {
                $q->where('airline', 'like', $term)
                    ->orWhereHas('package', function (Builder $p) use ($term) {
                        $p->where('name', 'like', $term)
                            ->orWhere('destination', 'like', $term);
                    })
                    ->orWhereHas('registrations', fn (Builder $r) => $r->where('name', 'like', $term));
            });
        }

        return $this->orderDepartureQuery($query);
    }

    /**
     * @return Builder<Departure>
     */
    protected function orderDepartureQuery(Builder $query): Builder
    {
        $dir = $this->dir;

        return match ($this->sort) {
            'package_name' => $query
                ->join('packages', 'departures.package_id', '=', 'packages.id')
                ->select('departures.*')
                ->orderBy('packages.name', $dir),
            'destination' => $query
                ->join('packages', 'departures.package_id', '=', 'packages.id')
                ->select('departures.*')
                ->orderBy('packages.destination', $dir)
                ->orderBy('packages.name', $dir),
            default => $query->orderBy('departure_date', $dir),
        };
    }

    /**
     * @return Builder<Package>
     */
    public function applyToPackageQuery(Builder $query): Builder
    {
        if ($this->destination !== null) {
            $query->where('destination', $this->destination);
        }

        if ($this->status !== null && in_array($this->status, ['active', 'archived'], true)) {
            $query->where('status', $this->status);
        }

        $dir = $this->dir;

        return match ($this->sort) {
            'destination' => $query->orderBy('destination', $dir)->orderBy('name', $dir),
            'departures_count' => $query->orderBy('departures_count', $dir),
            default => $query->orderBy('name', $dir),
        };
    }

    /**
     * @return Builder<\App\Models\Registration>
     */
    public function applyToRegistrationQuery(Builder $query): Builder
    {
        if ($this->packageId !== null) {
            $query->whereHas('departure', fn (Builder $q) => $q->where('package_id', $this->packageId));
        }

        if ($this->destination !== null) {
            $query->whereHas('departure.package', fn (Builder $q) => $q->where('destination', $this->destination));
        }

        if ($this->month !== null || $this->year !== null) {
            $query->whereHas('departure', function (Builder $q) {
                if ($this->month !== null) {
                    $q->whereMonth('departure_date', $this->month);
                }
                if ($this->year !== null) {
                    $q->whereYear('departure_date', $this->year);
                }
            });
        }

        $dir = $this->dir;

        return match ($this->sort) {
            'name' => $query->orderBy('registrations.name', $dir),
            'package_name' => $query
                ->join('departures', 'registrations.departure_id', '=', 'departures.id')
                ->join('packages', 'departures.package_id', '=', 'packages.id')
                ->select('registrations.*')
                ->orderBy('packages.name', $dir),
            default => $query
                ->join('departures', 'registrations.departure_id', '=', 'departures.id')
                ->select('registrations.*')
                ->orderBy('departures.departure_date', $dir),
        };
    }

    /**
     * @return Collection<int, string>
     */
    public static function destinations(): Collection
    {
        return Package::query()
            ->distinct()
            ->orderBy('destination')
            ->pluck('destination');
    }

    /**
     * @return Collection<int, Package>
     */
    public function packagesForDropdown(): Collection
    {
        $query = Package::query()->orderBy('name');

        if ($this->destination !== null) {
            $query->where('destination', $this->destination);
        }

        return $query->get(['id', 'name', 'destination']);
    }

    /**
     * @return array<int, string>
     */
    public static function months(): array
    {
        return self::MONTHS;
    }

    /**
     * @return list<int>
     */
    public static function years(): array
    {
        $years = [];
        for ($y = now()->year - 1; $y <= now()->year + 2; $y++) {
            $years[] = $y;
        }

        return $years;
    }
}
