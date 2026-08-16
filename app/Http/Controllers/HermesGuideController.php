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

        return view('hermes.updates', [
            'activities' => $activities,
            'packageFilter' => $request->string('package')->toString(),
            'monthFilter' => $request->integer('month'),
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
