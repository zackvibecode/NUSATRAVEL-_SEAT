<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Recent manual changes made through the app. Admin only.
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::query()->with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->string('action')->toString());
        }

        if ($request->filled('subject')) {
            $query->where('subject_type', $request->string('subject')->toString());
        }

        if ($request->filled('search')) {
            $term = '%'.strtolower(trim((string) $request->string('search'))).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(user_name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(subject_label) LIKE ?', [$term]);
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('activity-logs.index', [
            'logs' => $logs,
            'actionFilter' => $request->string('action')->toString(),
            'subjectFilter' => $request->string('subject')->toString(),
            'searchFilter' => $request->string('search')->toString(),
        ]);
    }
}
