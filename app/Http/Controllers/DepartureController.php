<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\Package;
use App\Support\TripListFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartureController extends Controller
{
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

        Departure::create($data);

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

        $departure->update($data);

        return redirect()->route('departures.show', $departure)->with('success', 'Departure updated successfully.');
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
