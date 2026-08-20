<?php

namespace App\Http\Controllers;

use App\Models\HermesSeatActivity;
use App\Models\ImportRun;
use App\Services\HermesDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HermesGuideController extends Controller
{
    public function index(): View
    {
        $importUrl = rtrim((string) config('app.url'), '/').'/api/imports/dropbox-excel';
        $apiBase = rtrim((string) config('app.url'), '/').'/api/hermes';
        $tokenConfigured = filled(config('services.import.token'));

        $recentRuns = Schema::hasTable('import_runs')
            ? ImportRun::query()->latest()->take(8)->get()
            : collect();

        return view('hermes.guide', compact('importUrl', 'apiBase', 'tokenConfigured', 'recentRuns'));
    }

    public function chat(): View
    {
        return view('hermes.chat');
    }

    public function updates(Request $request): View
    {
        $query = HermesSeatActivity::query()->latest();

        if ($request->filled('package')) {
            $query->where('package_name', 'like', '%'.$request->string('package').'%');
        }

        if ($request->filled('month')) {
            $query->whereMonth('departure_date', $request->integer('month'));
        }

        $activities = $query->paginate(30)->withQueryString();

        // Customer changes (new / updated / cancelled) since this user's last visit,
        // used for the "what's new" popup on page load.
        $seenAt = $request->session()->get('hermes_updates_seen_at');
        $freshActivities = collect();

        if ($seenAt) {
            try {
                $seenAt = \Illuminate\Support\Carbon::parse($seenAt);
            } catch (\Throwable) {
                $seenAt = null;
            }

            if ($seenAt) {
                $freshActivities = HermesSeatActivity::query()
                    ->whereIn('activity_type', [
                        'registration_created',
                        'registration_updated',
                        'registration_deleted',
                    ])
                    ->where('created_at', '>', $seenAt)
                    ->latest()
                    ->limit(50)
                    ->get();
            }
        }

        $request->session()->put('hermes_updates_seen_at', now());

        return view('hermes.updates', [
            'activities' => $activities,
            'packageFilter' => $request->string('package')->toString(),
            'monthFilter' => $request->integer('month'),
            'freshActivities' => $freshActivities,
            'seenAtLabel' => $seenAt ? $seenAt->timezone('Asia/Kuala_Lumpur')->format('D, j M Y g:ia') : null,
        ]);
    }

    public function chatMessage(Request $request, HermesDataService $hermes): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        return response()->json($hermes->chat($data['message']));
    }
}
