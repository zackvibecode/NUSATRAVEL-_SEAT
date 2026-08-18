<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Services\ActivityLogger;
use App\Support\TripListFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(private ActivityLogger $audit) {}

    public function index(Request $request): View
    {
        $filter = TripListFilter::fromRequest($request, 'packages', 'packages.index');

        $query = Package::query()->withCount(
            $filter->includePast
                ? 'departures'
                : ['departures' => fn ($q) => $q->notDeparted()],
        );
        $packages = $filter->applyToPackageQuery($query)->paginate(20);

        return view('packages.index', [
            'filter' => $filter,
            'packages' => $packages,
        ]);
    }

    public function create(): View
    {
        return view('packages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,archived'],
        ]);

        $package = Package::create($data);

        $this->audit->log('created', $package, [
            'name' => ['old' => null, 'new' => $data['name']],
            'destination' => ['old' => null, 'new' => $data['destination']],
            'status' => ['old' => null, 'new' => $data['status']],
        ]);

        return redirect()->route('packages.index')->with('success', 'Package created successfully.');
    }

    public function edit(Package $package): View
    {
        return view('packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,archived'],
        ]);

        $original = $package->getRawOriginal();

        $package->update($data);

        $this->audit->log('updated', $package, $this->audit->diff($original, $package));

        return redirect()->route('packages.index')->with('success', 'Package updated successfully.');
    }

    /**
     * Hard-delete the package. Related departures and registrations cascade.
     */
    public function destroy(Package $package): RedirectResponse
    {
        $name = $package->name;

        $this->audit->log('deleted', $package, [
            'name' => ['old' => $package->name, 'new' => null],
            'destination' => ['old' => $package->destination, 'new' => null],
            'status' => ['old' => $package->status, 'new' => null],
        ]);

        $package->delete();

        return redirect()->route('packages.index')
            ->with('success', "Package \"{$name}\" was deleted from the database. Related trips and registrations were removed.");
    }
}
