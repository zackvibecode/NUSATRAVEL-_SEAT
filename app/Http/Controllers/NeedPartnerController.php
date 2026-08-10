<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\View\View;

class NeedPartnerController extends Controller
{
    /**
     * List all registrations where Need Partner = Yes, with Male/Female counts (PRD 13).
     */
    public function index(): View
    {
        $registrations = Registration::with('departure.package')
            ->where('need_partner', true)
            ->orderByDesc('created_at')
            ->get();

        $maleCount = $registrations->where('partner_gender', 'male')->count();
        $femaleCount = $registrations->where('partner_gender', 'female')->count();

        return view('need-partner.index', compact('registrations', 'maleCount', 'femaleCount'));
    }
}
