<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    /**
     * Store a registration. Server-side over-capacity validation (PRD 9.2).
     */
    public function store(Request $request): RedirectResponse
    {
        $departure = Departure::findOrFail($request->integer('departure_id'));

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

        // Over-capacity check (PRD 9.2) — must reject on the server side
        $available = $departure->available_seats;

        if ($data['pax'] > $available) {
            return back()
                ->withInput()
                ->withErrors([
                    'pax' => "Only {$available} seats are currently available for this departure.",
                ]);
        }

        if (! $request->boolean('need_partner')) {
            $data['partner_gender'] = null;
        }

        Registration::create($data);

        return redirect()->route('departures.show', $departure)
            ->with('success', 'Registration added successfully.');
    }

    /**
     * Update a registration with the same over-capacity protection.
     */
    public function update(Request $request, Registration $registration): RedirectResponse
    {
        $departure = $registration->departure;

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

        // Recalculate available seats excluding this registration's current pax
        $otherPax = $departure->registrations()
            ->where('id', '!=', $registration->id)
            ->sum('pax');
        $available = $departure->total_seats - $otherPax;

        if ($data['pax'] > $available) {
            return back()
                ->withInput()
                ->withErrors([
                    'pax' => "Only {$available} seats are currently available for this departure.",
                ]);
        }

        if (! $request->boolean('need_partner')) {
            $data['partner_gender'] = null;
        }

        $registration->update($data);

        return redirect()->route('departures.show', $departure)
            ->with('success', 'Registration updated successfully.');
    }

    /**
     * Delete a registration (soft delete for audit trail).
     */
    public function destroy(Registration $registration): RedirectResponse
    {
        $departure = $registration->departure;

        $registration->delete();

        return redirect()->route('departures.show', $departure)
            ->with('success', 'Registration deleted successfully.');
    }
}
