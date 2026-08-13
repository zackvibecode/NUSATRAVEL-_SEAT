<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departure;
use App\Models\Package;
use App\Models\Registration;
use App\Services\HermesDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class HermesApiController extends Controller
{
    public function __construct(private HermesDataService $hermes) {}

    public function overview(): JsonResponse
    {
        return response()->json($this->hermes->overview());
    }

    public function chat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        return response()->json($this->hermes->chat($data['message']));
    }

    public function packages(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->hermes->listPackages($request->string('q')->toString() ?: null),
        ]);
    }

    public function showPackage(Package $package): JsonResponse
    {
        return response()->json($this->hermes->getPackage($package->id));
    }

    public function storePackage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,archived'],
        ]);

        $package = $this->hermes->createPackage($data);

        return response()->json($this->hermes->packagePayload($package), 201);
    }

    public function updatePackage(Request $request, Package $package): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'destination' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,archived'],
        ]);

        $package = $this->hermes->updatePackage($package, $data);

        return response()->json($this->hermes->packagePayload($package));
    }

    public function destroyPackage(Package $package): JsonResponse
    {
        $payload = $this->hermes->deletePackage($package);

        return response()->json([
            'message' => 'Package deleted from database. Related trips and registrations were removed.',
            'data' => $payload,
        ]);
    }

    public function departures(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->hermes->listDepartures($request->string('q')->toString() ?: null),
        ]);
    }

    public function showDeparture(Departure $departure): JsonResponse
    {
        return response()->json($this->hermes->getDeparture($departure->id));
    }

    public function storeDeparture(Request $request): JsonResponse
    {
        $data = $request->validate([
            'package_id' => ['required', 'exists:packages,id'],
            'departure_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after_or_equal:departure_date'],
            'total_seats' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'airline' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:open,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $departure = $this->hermes->createDeparture($data);

        return response()->json($this->hermes->departurePayload($departure), 201);
    }

    public function updateDeparture(Request $request, Departure $departure): JsonResponse
    {
        $data = $request->validate([
            'package_id' => ['sometimes', 'exists:packages,id'],
            'departure_date' => ['sometimes', 'date'],
            'return_date' => ['sometimes', 'date'],
            'total_seats' => ['sometimes', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'airline' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:open,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        $departure = $this->hermes->updateDeparture($departure, $data);

        return response()->json($this->hermes->departurePayload($departure));
    }

    public function destroyDeparture(Departure $departure): JsonResponse
    {
        $departure = $this->hermes->cancelDeparture($departure);

        return response()->json([
            'message' => 'Departure cancelled.',
            'data' => $this->hermes->departurePayload($departure),
        ]);
    }

    public function registrations(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->hermes->listRegistrations($request->string('q')->toString() ?: null),
        ]);
    }

    public function showRegistration(Registration $registration): JsonResponse
    {
        return response()->json($this->hermes->getRegistration($registration->id));
    }

    public function storeRegistration(Request $request): JsonResponse
    {
        $data = $request->validate([
            'departure_id' => ['required', 'exists:departures,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'pax' => ['nullable', 'integer', 'min:1'],
            'need_partner' => ['nullable', 'boolean'],
            'partner_gender' => ['nullable', 'in:male,female'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $registration = $this->hermes->createRegistration($data);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($this->hermes->registrationPayload($registration), 201);
    }

    public function updateRegistration(Request $request, Registration $registration): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'pax' => ['sometimes', 'integer', 'min:1'],
            'need_partner' => ['nullable', 'boolean'],
            'partner_gender' => ['nullable', 'in:male,female'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $registration = $this->hermes->updateRegistration($registration, $data);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($this->hermes->registrationPayload($registration));
    }

    public function destroyRegistration(Registration $registration): JsonResponse
    {
        $id = $registration->id;
        $this->hermes->deleteRegistration($registration);

        return response()->json([
            'message' => 'Registration deleted.',
            'id' => $id,
        ]);
    }
}
