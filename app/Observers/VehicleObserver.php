<?php

namespace App\Observers;

use App\Models\Vehicle;
use Illuminate\Support\Str;

class VehicleObserver
{
    public function created(Vehicle $vehicle): void
    {
        //
    }

    public function updated(Vehicle $vehicle): void
    {
        //
    }

    public function saving(Vehicle $vehicle): void
    {
        $vehicle->plate_number = Str::upper($vehicle->plate_number);
    }

    public function deleted(Vehicle $vehicle): void
    {
        //
    }

    public function restored(Vehicle $vehicle): void
    {
        //
    }

    public function forceDeleted(Vehicle $vehicle): void
    {
        //
    }
}
