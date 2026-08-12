<?php

namespace App\Http\Controllers;

use App\Models\ImportRun;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HermesGuideController extends Controller
{
    public function index(): View
    {
        $importUrl = rtrim((string) config('app.url'), '/').'/api/imports/dropbox-excel';
        $tokenConfigured = filled(config('services.import.token'));

        $recentRuns = Schema::hasTable('import_runs')
            ? ImportRun::query()->latest()->take(8)->get()
            : collect();

        return view('hermes.guide', compact('importUrl', 'tokenConfigured', 'recentRuns'));
    }
}
