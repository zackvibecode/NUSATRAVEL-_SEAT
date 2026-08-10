<?php

namespace App\Http\Controllers;

use App\Models\Registration;
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
        $query = Registration::with('departure.package');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('departure.package', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        $registrations = $query->orderByDesc('created_at')->get();

        return view('participants.index', [
            'registrations' => $registrations,
            'search' => $search,
        ]);
    }
}
