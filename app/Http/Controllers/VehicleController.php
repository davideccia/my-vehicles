<?php

namespace App\Http\Controllers;

use App\Enums\VehicleFuelTypeEnum;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('vehicles/Index', [
            'vehicles' => VehicleResource::collection(Vehicle::paginate(5)),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('vehicles/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'fuel_type' => ['required', Rule::enum(VehicleFuelTypeEnum::class)],
            'purchase_date' => ['nullable', 'date'],
        ]);

        Vehicle::create($validated);

        return redirect()->route('vehicles.index')->with('success', 'Veicolo salvato.');
    }

    public function show(Vehicle $vehicle): Response
    {
        return Inertia::render('vehicles/Show', [
            'vehicle' => (new VehicleResource($vehicle))->resolve(),
        ]);
    }

    public function edit(Vehicle $vehicle): Response
    {
        return Inertia::render('vehicles/Edit', [
            'vehicle' => (new VehicleResource($vehicle))->resolve(),
        ]);
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number,'.$vehicle->id],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'fuel_type' => ['required', Rule::enum(VehicleFuelTypeEnum::class)],
            'purchase_date' => ['nullable', 'date'],
        ]);

        $vehicle->update($validated);

        return redirect()->route('vehicles.index')->with('success', 'Veicolo aggiornato.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Veicolo eliminato.');
    }
}
