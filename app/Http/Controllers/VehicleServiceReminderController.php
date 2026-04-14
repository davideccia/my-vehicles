<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleResource;
use App\Http\Resources\VehicleServiceReminderResource;
use App\Http\Resources\VehicleServiceTypeResource;
use App\Models\Vehicle;
use App\Models\VehicleServiceReminder;
use App\Models\VehicleServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehicleServiceReminderController extends Controller
{
    public function index(Request $request): Response
    {
        $vehicles = Vehicle::all();
        $query = VehicleServiceReminder::with([
            'vehicle.latestVehicleService',
            'vehicle.latestVehicleRefuel',
            'vehicleServiceType.latestVehicleService',
        ]);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        return Inertia::render('vehicle-service-reminders/Index', [
            'reminders' => VehicleServiceReminderResource::collection($query->get())->resolve(),
            'vehicles' => VehicleResource::collection($vehicles)->resolve(),
            'selectedVehicleId' => $request->input('vehicle_id'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('vehicle-service-reminders/Create', [
            'vehicles' => VehicleResource::collection(Vehicle::all())->resolve(),
            'serviceTypes' => VehicleServiceTypeResource::collection(VehicleServiceType::all())->resolve(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'uuid', 'exists:vehicles,id'],
            'vehicle_service_type_id' => ['required', 'uuid', 'exists:vehicle_service_types,id'],
            'every' => ['required', 'integer', 'min:1'],
        ]);

        VehicleServiceReminder::create($validated);

        return redirect()->route('vehicle-service-reminders.index')->with('success', 'Promemoria salvato.');
    }

    public function edit(VehicleServiceReminder $vehicleServiceReminder): Response
    {
        $vehicleServiceReminder->load(['vehicle.latestVehicleService', 'vehicle.latestVehicleRefuel']);

        return Inertia::render('vehicle-service-reminders/Edit', [
            'reminder' => (new VehicleServiceReminderResource($vehicleServiceReminder))->resolve(),
            'vehicles' => VehicleResource::collection(Vehicle::all())->resolve(),
            'serviceTypes' => VehicleServiceTypeResource::collection(VehicleServiceType::all())->resolve(),
        ]);
    }

    public function update(Request $request, VehicleServiceReminder $vehicleServiceReminder): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'uuid', 'exists:vehicles,id'],
            'vehicle_service_type_id' => ['required', 'uuid', 'exists:vehicle_service_types,id'],
            'every' => ['required', 'integer', 'min:1'],
        ]);

        $vehicleServiceReminder->update($validated);

        return redirect()->route('vehicle-service-reminders.index')->with('success', 'Promemoria aggiornato.');
    }

    public function destroy(VehicleServiceReminder $vehicleServiceReminder): RedirectResponse
    {
        $vehicleServiceReminder->delete();

        return redirect()->route('vehicle-service-reminders.index')->with('success', 'Promemoria eliminato.');
    }
}
