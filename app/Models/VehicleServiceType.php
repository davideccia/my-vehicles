<?php

namespace App\Models;

use App\Models\Scopes\VehicleServiceTypeDefaultSortScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['icon', 'label'])]
#[ScopedBy(VehicleServiceTypeDefaultSortScope::class)]
class VehicleServiceType extends Model
{
    use HasFactory, HasUuids;

    public function vehicleServices(): HasMany
    {
        return $this->hasMany(VehicleService::class);
    }

    public function latestVehicleService(): HasOne
    {
        return $this->vehicleServices()->one()->latestOfMany('date');
    }
}
