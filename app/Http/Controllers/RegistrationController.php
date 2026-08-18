<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\Registration;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function __construct(private ActivityLogger $audit) {}

    /**
     * Store a registration. Server-side over-capacity validation (PRD 9.2).
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'departure_id' => ['required', 'exists:departures,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'pax' => ['required', 'integer', 'min:1'],
            'payment_status' => ['nullable', 'in:pending,deposit,paid'],
            'need_partner' => ['sometimes', 'boolean'],
            'partner_gender' => ['nullable', 'in:male,female'],
            'notes' => ['nullable', 'string'],
        ]);

        // Partner gender is required only when Need Partner = Yes (PRD 9.1)
        if ($request->boolean('need_partner') && ! $request->filled('partner_gender')) {
            return back()
                ->withInput()
                ->withErrors(['partner_gender' => 'Partner gender is required when Need Partner is Yes.']);
        }

        if (! $request->boolean('need_partner')) {
            $data['partner_gender'] = null;
        }

        try {
            DB::transaction(function () use ($data, &$departure): void {
                // Lock the departure row so concurrent submissions cannot overbook (PRD 9.2).
                $departure = Departure::query()->lockForUpdate()->findOrFail((int) $data['departure_id']);

                $available = $departure->available_seats;

                if ($data['pax'] > $available) {
                    throw new \RuntimeException("Only {$available} seats are currently available for this departure.");
                }

                $registration = Registration::create($data);

                $this->audit->log('created', $registration, collect($data)
                    ->reject(fn ($_, $key) => in_array($key, ['departure_id'], true))
                    ->map(fn ($value) => ['old' => null, 'new' => $value])
                    ->all());
            });
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['pax' => $e->getMessage()]);
        }

        return redirect()->route('departures.show', $departure)
            ->with('success', 'Registration added successfully.');
    }

    /**
     * Update a registration with the same over-capacity protection.
     */
    public function update(Request $request, Registration $registration): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'pax' => ['required', 'integer', 'min:1'],
            'payment_status' => ['nullable', 'in:pending,deposit,paid'],
            'need_partner' => ['sometimes', 'boolean'],
            'partner_gender' => ['nullable', 'in:male,female'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->boolean('need_partner') && ! $request->filled('partner_gender')) {
            return back()
                ->withInput()
                ->withErrors(['partner_gender' => 'Partner gender is required when Need Partner is Yes.']);
        }

        if (! $request->boolean('need_partner')) {
            $data['partner_gender'] = null;
        }

        $original = $registration->getRawOriginal();

        try {
            DB::transaction(function () use ($data, $registration): void {
                // Lock the departure row so concurrent edits cannot overbook.
                $departure = Departure::query()->lockForUpdate()->findOrFail($registration->departure_id);

                // Recalculate available seats excluding this registration's current pax
                $otherPax = $departure->registrations()
                    ->where('id', '!=', $registration->id)
                    ->sum('pax');
                $available = $departure->total_seats - $otherPax;

                if ($data['pax'] > $available) {
                    throw new \RuntimeException("Only {$available} seats are currently available for this departure.");
                }

                $registration->update($data);
            });
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['pax' => $e->getMessage()]);
        }

        $this->audit->log('updated', $registration, $this->audit->diff($original, $registration));

        return redirect()->route('departures.show', $registration->departure_id)
            ->with('success', 'Registration updated successfully.');
    }

    /**
     * Delete a registration (soft delete for audit trail).
     */
    public function destroy(Registration $registration): RedirectResponse
    {
        $departure = $registration->departure;

        $this->audit->log('deleted', $registration, [
            'name' => ['old' => $registration->name, 'new' => null],
            'pax' => ['old' => $registration->pax, 'new' => null],
            'payment_status' => ['old' => $registration->payment_status, 'new' => null],
        ]);

        $registration->delete();

        return redirect()->route('departures.show', $departure)
            ->with('success', 'Registration deleted successfully.');
    }
}
