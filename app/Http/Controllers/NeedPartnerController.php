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

        $registrations = $filter->applyToRegistrationQuery($query)->paginate(20);

        // Count from ALL matching records, not just current page
        $allMatching = Registration::where('need_partner', true)->get();
        $maleCount = $allMatching->where('partner_gender', 'male')->count();
        $femaleCount = $allMatching->where('partner_gender', 'female')->count();

        // Auto-match suggestions: same departure, same gender, both need partner
        $matches = [];
        $grouped = $allMatching->groupBy('departure_id');
        foreach ($grouped as $departureId => $group) {
            $males = $group->where('partner_gender', 'male')->values();
            $females = $group->where('partner_gender', 'female')->values();

            // Match males with males, females with females (same gender pairing)
            for ($i = 0; $i < $males->count(); $i += 2) {
                if (isset($males[$i + 1])) {
                    $matches[] = [
                        'type' => 'male',
                        'departure' => $males[$i]->departure,
                        'pair' => [$males[$i], $males[$i + 1]],
                    ];
                }
            }
            for ($i = 0; $i < $females->count(); $i += 2) {
                if (isset($females[$i + 1])) {
                    $matches[] = [
                        'type' => 'female',
                        'departure' => $females[$i]->departure,
                        'pair' => [$females[$i], $females[$i + 1]],
                    ];
                }
            }
        }

        return view('need-partner.index', [
            'filter' => $filter,
            'registrations' => $registrations,
            'maleCount' => $maleCount,
            'femaleCount' => $femaleCount,
            'matches' => collect($matches)->take(10),
        ]);
    }
}
