<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DropboxExcelImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function dropboxExcel(Request $request, DropboxExcelImportService $importer): JsonResponse
    {
        $data = $request->validate([
            'filename' => ['nullable', 'string', 'max:255'],
            'dropbox_path' => ['nullable', 'string', 'max:500'],
            'packages' => ['nullable', 'array'],
            'packages.*.name' => ['required_with:packages', 'string', 'max:255'],
            'packages.*.destination' => ['required_with:packages', 'string', 'max:255'],
            'packages.*.description' => ['nullable', 'string'],
            'packages.*.status' => ['nullable', 'in:active,archived'],
            'departures' => ['nullable', 'array'],
            'departures.*.package_name' => ['nullable', 'string', 'max:255'],
            'departures.*.package' => ['nullable', 'string', 'max:255'],
            'departures.*.destination' => ['nullable', 'string', 'max:255'],
            'departures.*.departure_date' => ['required_with:departures', 'date'],
            'departures.*.return_date' => ['required_with:departures', 'date'],
            'departures.*.total_seats' => ['nullable', 'integer', 'min:1'],
            'departures.*.price' => ['nullable', 'numeric', 'min:0'],
            'departures.*.airline' => ['nullable', 'string', 'max:255'],
            'departures.*.status' => ['nullable', 'in:open,cancelled'],
            'departures.*.notes' => ['nullable', 'string'],
            'registrations' => ['nullable', 'array'],
            'registrations.*.name' => ['nullable', 'string', 'max:255'],
            'registrations.*.customer_name' => ['nullable', 'string', 'max:255'],
            'registrations.*.phone' => ['nullable', 'string', 'max:50'],
            'registrations.*.pax' => ['nullable', 'integer', 'min:1'],
            'registrations.*.need_partner' => ['nullable'],
            'registrations.*.partner_gender' => ['nullable', 'in:male,female'],
            'registrations.*.notes' => ['nullable', 'string'],
            'registrations.*.package_name' => ['nullable', 'string', 'max:255'],
            'registrations.*.package' => ['nullable', 'string', 'max:255'],
            'registrations.*.destination' => ['nullable', 'string', 'max:255'],
            'registrations.*.departure_date' => ['nullable', 'date'],
            'registrations.*.departure_id' => ['nullable', 'integer'],
            // Invoice / payment / PIC fields (synced from source API)
            'registrations.*.invoice_no' => ['nullable', 'string', 'max:255'],
            'registrations.*.pic_utama' => ['nullable', 'string', 'max:255'],
            'registrations.*.pic_in_house' => ['nullable', 'string', 'max:255'],
            'registrations.*.invoice_status' => ['nullable', 'string', 'max:255'],
            'registrations.*.invoice_amount' => ['nullable', 'numeric', 'min:0'],
            'registrations.*.total_paid' => ['nullable', 'numeric', 'min:0'],
            'registrations.*.invoice_url' => ['nullable', 'string', 'max:500'],
        ]);

        if (
            empty($data['packages'])
            && empty($data['departures'])
            && empty($data['registrations'])
        ) {
            return response()->json([
                'message' => 'Provide at least one of: packages, departures, registrations.',
            ], 422);
        }

        foreach ($data['departures'] ?? [] as $i => $dep) {
            $pkg = trim((string) ($dep['package_name'] ?? $dep['package'] ?? ''));
            if ($pkg === '') {
                return response()->json([
                    'message' => "departures.{$i}: package_name (or package) is required.",
                ], 422);
            }
        }

        foreach ($data['registrations'] ?? [] as $i => $reg) {
            $name = trim((string) ($reg['name'] ?? $reg['customer_name'] ?? ''));
            if ($name === '') {
                return response()->json([
                    'message' => "registrations.{$i}: name (or customer_name) is required.",
                ], 422);
            }
            if (empty($reg['departure_id']) && empty($reg['departure_date'])) {
                return response()->json([
                    'message' => "registrations.{$i}: departure_date or departure_id is required.",
                ], 422);
            }
        }

        $dryRun = $request->boolean('dry_run');
        $result = $importer->import($data, $dryRun);

        $http = $result['status'] === 'failed' ? 500 : 200;

        return response()->json($result, $http);
    }
}
