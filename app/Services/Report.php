<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleRefuel;
use Carbon\CarbonPeriodImmutable;
use Illuminate\Support\Collection;

class Report
{
    public static function fuelCosts(string $from, string $to, string|Vehicle|null $vehicle = null): array
    {
        $format = __('app.date_format');
        $period = CarbonPeriodImmutable::create($from, $to);

        $query = VehicleRefuel::with(['vehicle'])
            ->orderBy('date')
            ->groupBy(['date', 'vehicle_id'])
            ->whereBetween('date', [$period->getStartDate()->toDateString(), $period->getEndDate()->toDateString()])
            ->selectRaw('MAX(unit_price) as max_unit_price, date, vehicle_id');

        if ($vehicle !== null) {
            $vehicle = is_string($vehicle) ? Vehicle::find($vehicle) : $vehicle;
            $query->where('vehicle_id', $vehicle->id);
        }

        $refuels = $query->get();

        $dates = $refuels
            ->pluck('date')
            ->unique()
            ->sort()
            ->map(static fn ($date) => $date->format($format))
            ->values()
            ->all();

        $datasets = $refuels
            ->groupBy('vehicle_id')
            ->map(static function (Collection $vehicleRefuels) use ($dates, $format) {
                /** @var Vehicle $vehicle */
                $vehicle = $vehicleRefuels->firstWhere('vehicle', '!==', null)->vehicle;

                $byDate = $vehicleRefuels->mapWithKeys(static fn ($model) => [$model->date->format($format) => $model->max_unit_price]);

                return [
                    'label' => $vehicle->full_name,
                    'backgroundColor' => $vehicle->color,
                    'borderColor' => $vehicle->color,
                    'data' => array_map(static fn ($date) => $byDate->get($date) ?? 0, $dates),
                ];
            })
            ->values()
            ->sortBy('label')
            ->values();

        return [
            'labels' => $dates,
            'datasets' => $datasets->toArray(),
        ];
    }
}
