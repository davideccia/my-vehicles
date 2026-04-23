<?php

use App\Models\Vehicle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('fuel_type')->after('color')->nullable();
        });

        Vehicle::query()->update(['fuel_type' => 'diesel']);

        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('fuel_type')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('fuel_type');
        });
    }
};
