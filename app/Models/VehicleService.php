<?php

namespace App\Models;

use App\Models\Scopes\VehicleServiceDefaultSortScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vehicle_id', 'vehicle_service_type_id', 'vehicle_service_reminder_id', 'date', 'total_paid', 'odometer', 'location', 'notes'])]
#[ScopedBy(VehicleServiceDefaultSortScope::class)]
class VehicleService extends Model
{
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vehicleServiceType(): BelongsTo
    {
        return $this->belongsTo(VehicleServiceType::class);
    }

    public function vehicleServiceReminder(): BelongsTo
    {
        return $this->belongsTo(VehicleServiceReminder::class);
    }
}
