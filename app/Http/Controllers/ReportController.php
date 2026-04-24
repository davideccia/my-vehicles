<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\Reports;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function show(Request $request): Response
    {
        $validated = $request->validate([
            'vehicle_id' => ['nullable', Rule::exists('vehicles', 'id')],
            'from' => ['nullable', Rule::date()->format('Y-m-d')],
            'to' => ['nullable', Rule::date()->format('Y-m-d')],
        ]);

        $from = $validated['from'] ?? now()->startOfWeek()->toDateString();
        $to = $validated['to'] ?? now()->endOfWeek()->toDateString();

        return Inertia::render('Reports', [
            'vehicles' => VehicleResource::collection(Vehicle::all())->resolve(),
            'filters' => [
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'from' => $from,
                'to' => $to,
            ],
            'fuel_costs' => Reports::init($from, $to, ($validated['vehicle_id'] ?? null))->vehiclesFuelCosts(),
        ]);
    }
}
