<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Support\TripListFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(Request $request): View
    {
        $filter = TripListFilter::fromRequest($request, 'packages', 'packages.index');

        $query = Package::query()->withCount('departures');
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

        Package::create($data);

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

        $package->update($data);

        return redirect()->route('packages.index')->with('success', 'Package updated successfully.');
    }

    /**
     * Hard-delete the package. Related departures and registrations cascade.
     */
    public function destroy(Package $package): RedirectResponse
    {
        $name = $package->name;
        $package->delete();

        return redirect()->route('packages.index')
            ->with('success', "Pakej \"{$name}\" telah dipadam dari database.");
    }
}
