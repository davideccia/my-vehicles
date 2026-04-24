<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleRefuelResource;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Models\VehicleRefuel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehicleRefuelController extends Controller
{
    public function index(Request $request): Response
    {
        $vehicles = Vehicle::all();
        $query = VehicleRefuel::with('vehicle');

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        return Inertia::render('vehicle-refuels/Index', [
            'refuels' => VehicleRefuelResource::collection($query->paginate(5)),
            'vehicles' => VehicleResource::collection($vehicles)->resolve(),
            'selectedVehicleId' => $request->input('vehicle_id'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('vehicle-refuels/Create', [
            'vehicles' => VehicleResource::collection(Vehicle::all())->resolve(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'uuid', 'exists:vehicles,id'],
            'date' => ['required', 'date'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'liters' => ['required', 'numeric', 'min:0'],
            'odometer' => ['required', 'integer', 'min:0'],
        ]);

        VehicleRefuel::create($validated);

        return redirect()->route('vehicle-refuels.index')->with('success', 'Rifornimento salvato.');
    }

    public function show(VehicleRefuel $vehicleRefuel): Response
    {
        return Inertia::render('vehicle-refuels/Show', [
            'refuel' => (new VehicleRefuelResource($vehicleRefuel->load('vehicle')))->resolve(),
        ]);
    }

    public function edit(VehicleRefuel $vehicleRefuel): Response
    {
        return Inertia::render('vehicle-refuels/Edit', [
            'refuel' => (new VehicleRefuelResource($vehicleRefuel))->resolve(),
            'vehicles' => VehicleResource::collection(Vehicle::all())->resolve(),
        ]);
    }

    public function update(Request $request, VehicleRefuel $vehicleRefuel): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'uuid', 'exists:vehicles,id'],
            'date' => ['required', 'date'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'liters' => ['required', 'numeric', 'min:0'],
            'odometer' => ['required', 'integer', 'min:0'],
        ]);

        $vehicleRefuel->update($validated);

        return redirect()->route('vehicle-refuels.index')->with('success', 'Rifornimento aggiornato.');
    }

    public function destroy(VehicleRefuel $vehicleRefuel): RedirectResponse
    {
        $vehicleRefuel->delete();

        return redirect()->route('vehicle-refuels.index')->with('success', 'Rifornimento eliminato.');
    }
}
