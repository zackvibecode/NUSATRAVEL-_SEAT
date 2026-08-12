<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Support\TripListFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NeedPartnerController extends Controller
{
    /**
     * List all registrations where Need Partner = Yes, with Male/Female counts (PRD 13).
     */
    public function index(Request $request): View
    {
        $filter = TripListFilter::fromRequest($request, 'need_partner', 'need-partner.index');

        $query = Registration::query()
            ->with('departure.package')
            ->where('need_partner', true);

        $registrations = $filter->applyToRegistrationQuery($query)->get();

        $maleCount = $registrations->where('partner_gender', 'male')->count();
        $femaleCount = $registrations->where('partner_gender', 'female')->count();

        return view('need-partner.index', [
            'filter' => $filter,
            'registrations' => $registrations,
            'maleCount' => $maleCount,
            'femaleCount' => $femaleCount,
        ]);
    }
}
