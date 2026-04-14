<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleServiceTypeResource;
use App\Models\VehicleServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehicleServiceTypeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('vehicle-service-types/Index', [
            'serviceTypes' => VehicleServiceTypeResource::collection(VehicleServiceType::all())->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('vehicle-service-types/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'icon' => ['required', 'string', 'max:100'],
            'label' => ['required', 'string', 'max:100'],
        ]);

        VehicleServiceType::create($validated);

        return redirect()->route('vehicle-service-types.index')->with('success', 'Tipo salvato.');
    }

    public function edit(VehicleServiceType $vehicleServiceType): Response
    {
        return Inertia::render('vehicle-service-types/Edit', [
            'serviceType' => (new VehicleServiceTypeResource($vehicleServiceType))->resolve(),
        ]);
    }

    public function update(Request $request, VehicleServiceType $vehicleServiceType): RedirectResponse
    {
        $validated = $request->validate([
            'icon' => ['required', 'string', 'max:100'],
            'label' => ['required', 'string', 'max:100'],
        ]);

        $vehicleServiceType->update($validated);

        return redirect()->route('vehicle-service-types.index')->with('success', 'Tipo aggiornato.');
    }

    public function destroy(VehicleServiceType $vehicleServiceType): RedirectResponse
    {
        $vehicleServiceType->delete();

        return redirect()->route('vehicle-service-types.index')->with('success', 'Tipo eliminato.');
    }
}
