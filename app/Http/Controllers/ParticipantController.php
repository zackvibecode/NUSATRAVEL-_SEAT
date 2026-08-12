<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Support\TripListFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    /**
     * All registrations with optional search (PRD section 15).
     * Search supports: package name, participant name, phone number.
     */
    public function index(Request $request): View
    {
        $filter = TripListFilter::fromRequest($request, 'participants', 'participants.index');
        $search = $request->input('search');

        $query = Registration::query()->with('departure.package');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('departure.package', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        $registrations = $filter->applyToRegistrationQuery($query)->get();

        return view('participants.index', [
            'filter' => $filter,
            'registrations' => $registrations,
            'search' => $search,
        ]);
    }
}
