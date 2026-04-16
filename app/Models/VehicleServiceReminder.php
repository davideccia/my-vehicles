<?php

namespace App\Models;

use App\Models\Scopes\VehicleServiceReminderDefaultSortScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vehicle_id', 'vehicle_service_type_id', 'every'])]
#[ScopedBy(VehicleServiceReminderDefaultSortScope::class)]
class VehicleServiceReminder extends Model
{
    use HasFactory, HasUuids;

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vehicleServiceType(): BelongsTo
    {
        return $this->belongsTo(VehicleServiceType::class);
    }

    public function latestVehicleServiceOdometer(): int
    {
        return VehicleService::query()
            ->where('vehicle_services.vehicle_id', $this->vehicle_id)
            ->where('vehicle_services.vehicle_service_type_id', $this->vehicle_service_type_id)
            ->orderByDesc('vehicle_services.date')
            ->first()?->odometer ?? 0;
    }

    public function recommendedVehicleServiceOdometer(): int
    {
        return $this->latestVehicleServiceOdometer() + $this->every;
    }

    public function overdueOdometerDiff(): int
    {
        return $this->recommendedVehicleServiceOdometer() - $this->vehicle->currentOdometer();
    }

    public function isOverDue(): bool
    {
        return $this->vehicle->currentOdometer() >= $this->recommendedVehicleServiceOdometer();
    }
}
