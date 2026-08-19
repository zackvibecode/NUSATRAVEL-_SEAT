<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\Package;
use App\Services\ActivityLogger;
use App\Support\TripListFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartureController extends Controller
{
    public function __construct(private ActivityLogger $audit) {}

    public function index(Request $request): View
    {
        $filter = TripListFilter::fromRequest($request, 'departures', 'departures.index');

        $query = Departure::query()
            ->with('package')
            ->withSum('registrations as registered_pax_sum', 'pax');

        $departures = $filter->applyToDepartureQuery($query)->paginate(20);

        return view('departures.index', [
            'filter' => $filter,
            'departures' => $departures,
        ]);
    }

    public function create(): View
    {
        $packages = Package::active()->orderBy('name')->get();

        return view('departures.create', compact('packages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'departure_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after_or_equal:departure_date'],
            'total_seats' => ['required', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'airline' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:open,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['status'] ??= 'open';

        $departure = Departure::create($data);

        $this->audit->log('created', $departure, [
            'departure_date' => ['old' => null, 'new' => $departure->departure_date?->format('d M Y')],
            'total_seats' => ['old' => null, 'new' => $departure->total_seats],
            'status' => ['old' => null, 'new' => $departure->status],
        ]);

        return redirect()->route('departures.index')->with('success', 'Departure created successfully.');
    }

    public function show(Departure $departure): View
    {
        $departure->load(['package', 'registrations' => fn ($q) => $q->latest()]);

        return view('departures.show', [
            'departure' => $departure,
            'registrations' => $departure->registrations,
        ]);
    }

    public function edit(Departure $departure): View
    {
        $packages = Package::active()->orderBy('name')->get();

        return view('departures.edit', compact('departure', 'packages'));
    }

    public function update(Request $request, Departure $departure): RedirectResponse
    {
        $data = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'departure_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after_or_equal:departure_date'],
            'total_seats' => ['required', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'airline' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:open,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $original = $departure->getRawOriginal();

        $departure->update($data);

        $this->audit->log('updated', $departure, $this->audit->diff($original, $departure));

        return redirect()->route('departures.show', $departure)->with('success', 'Departure updated successfully.');
    }

    /**
     * Hard-delete the departure. Related registrations cascade.
     */
    public function destroy(Departure $departure): RedirectResponse
    {
        $label = $departure->package->name.' — '.$departure->departure_date->format('d M Y');

        $this->audit->log('deleted', $departure, [
            'departure_date' => ['old' => $departure->departure_date?->format('d M Y'), 'new' => null],
            'total_seats' => ['old' => $departure->total_seats, 'new' => null],
            'status' => ['old' => $departure->status, 'new' => null],
            'registrations_removed' => ['old' => (string) $departure->registrations()->count(), 'new' => null],
        ]);

        $departure->delete();

        return redirect()->route('departures.index')
            ->with('success', "Trip \"{$label}\" was deleted. Its registrations were removed too.");
    }

    /**
     * Print-friendly manifest for tour guides.
     */
    public function manifest(Departure $departure): View
    {
        $departure->load(['package', 'registrations' => fn ($q) => $q->orderBy('name')]);

        return view('departures.manifest', [
            'departure' => $departure,
            'registrations' => $departure->registrations,
        ]);
    }
}
