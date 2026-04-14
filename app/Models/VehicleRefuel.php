<?php

namespace App\Models;

use App\Models\Scopes\VehicleRefuelDefaultSortScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vehicle_id', 'date', 'total_price', 'unit_price', 'liters', 'odometer'])]
#[ScopedBy(VehicleRefuelDefaultSortScope::class)]
class VehicleRefuel extends Model
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
}
