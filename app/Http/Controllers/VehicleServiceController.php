<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleResource;
use App\Http\Resources\VehicleServiceResource;
use App\Http\Resources\VehicleServiceTypeResource;
use App\Models\Vehicle;
use App\Models\VehicleService;
use App\Models\VehicleServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehicleServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $vehicles = Vehicle::all();
        $query = VehicleService::with(['vehicle', 'vehicleServiceType']);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->input('to'));
        }

        return Inertia::render('vehicle-services/Index', [
            'services' => VehicleServiceResource::collection($query->paginate(5)),
            'vehicles' => VehicleResource::collection($vehicles)->resolve(),
            'selectedVehicleId' => $request->input('vehicle_id'),
            'selectedFrom' => $request->input('from'),
            'selectedTo' => $request->input('to'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('vehicle-services/Create', [
            'vehicles' => VehicleResource::collection(Vehicle::all())->resolve(),
            'serviceTypes' => VehicleServiceTypeResource::collection(VehicleServiceType::all())->resolve(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'uuid', 'exists:vehicles,id'],
            'vehicle_service_type_id' => ['required', 'uuid', 'exists:vehicle_service_types,id'],
            'date' => ['required', 'date'],
            'total_paid' => ['required', 'numeric', 'min:0'],
            'odometer' => ['required', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        VehicleService::create($validated);

        return redirect()->route('vehicle-services.index')->with('success', 'Manutenzione salvata.');
    }

    public function show(VehicleService $vehicleService): Response
    {
        return Inertia::render('vehicle-services/Show', [
            'service' => (new VehicleServiceResource($vehicleService->load(['vehicle', 'vehicleServiceType'])))->resolve(),
        ]);
    }

    public function edit(VehicleService $vehicleService): Response
    {
        return Inertia::render('vehicle-services/Edit', [
            'service' => (new VehicleServiceResource($vehicleService))->resolve(),
            'vehicles' => VehicleResource::collection(Vehicle::all())->resolve(),
            'serviceTypes' => VehicleServiceTypeResource::collection(VehicleServiceType::all())->resolve(),
        ]);
    }

    public function update(Request $request, VehicleService $vehicleService): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'uuid', 'exists:vehicles,id'],
            'vehicle_service_type_id' => ['required', 'uuid', 'exists:vehicle_service_types,id'],
            'date' => ['required', 'date'],
            'total_paid' => ['required', 'numeric', 'min:0'],
            'odometer' => ['required', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $vehicleService->update($validated);

        return redirect()->route('vehicle-services.index')->with('success', 'Manutenzione aggiornata.');
    }

    public function destroy(VehicleService $vehicleService): RedirectResponse
    {
        $vehicleService->delete();

        return redirect()->route('vehicle-services.index')->with('success', 'Manutenzione eliminata.');
    }
}
