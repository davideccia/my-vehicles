<?php

namespace App\Models;

use App\Models\Scopes\VehicleDefaultSortScope;
use App\Observers\VehicleObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy(VehicleObserver::class)]
#[Fillable(['plate_number', 'brand', 'model', 'year', 'purchase_date'])]
#[ScopedBy(VehicleDefaultSortScope::class)]
class Vehicle extends Model
{
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
        ];
    }

    public function vehicleRefuels(): HasMany
    {
        return $this->hasMany(VehicleRefuel::class);
    }

    public function vehicleServices(): HasMany
    {
        return $this->hasMany(VehicleService::class);
    }

    public function vehicleServiceReminders(): HasMany
    {
        return $this->hasMany(VehicleServiceReminder::class);
    }

    public function latestVehicleService(): HasOne
    {
        return $this->hasOne(VehicleService::class)->latestOfMany('date');
    }

    public function latestVehicleRefuel(): HasOne
    {
        return $this->hasOne(VehicleRefuel::class)->latestOfMany('date');
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "[{$this->plate_number}] {$this->brand} {$this->model}"
        );
    }

    public function currentOdometer(): int
    {
        return max(
            $this->latestVehicleService?->odometer ?? 0,
            $this->latestVehicleRefuel?->odometer ?? 0
        );
    }
}
