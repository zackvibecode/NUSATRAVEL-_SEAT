<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * Simple monthly calendar view of departures.
     */
    public function index(Request $request): View
    {
        $month = $request->filled('month') ? (int) $request->input('month') : now()->month;
        $year = $request->filled('year') ? (int) $request->input('year') : now()->year;

        $start = now()->setYear($year)->setMonth($month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $departures = Departure::whereBetween('departure_date', [$start, $end])
            ->with('package')
            ->withSum('registrations as registered_pax_sum', 'pax')
            ->orderBy('departure_date')
            ->get()
            ->groupBy(fn ($d) => $d->departure_date->format('Y-m-d'));

        // Build calendar grid
        $firstDayOfWeek = $start->dayOfWeek; // 0 = Sunday
        $daysInMonth = $start->daysInMonth;

        $weeks = [];
        $currentDay = 1;
        $totalCells = $firstDayOfWeek + $daysInMonth;
        $totalWeeks = (int) ceil($totalCells / 7);

        for ($w = 0; $w < $totalWeeks; $w++) {
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $cellIndex = ($w * 7) + $d;
                if ($cellIndex < $firstDayOfWeek || $currentDay > $daysInMonth) {
                    $week[] = null;
                } else {
                    $date = $start->copy()->setDay($currentDay);
                    $key = $date->format('Y-m-d');
                    $week[] = [
                        'day' => $currentDay,
                        'date' => $date,
                        'departures' => $departures->get($key, collect()),
                    ];
                    $currentDay++;
                }
            }
            $weeks[] = $week;
        }

        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        return view('calendar.index', [
            'weeks' => $weeks,
            'month' => $month,
            'year' => $year,
            'monthLabel' => $months[$month],
            'months' => $months,
        ]);
    }
}
