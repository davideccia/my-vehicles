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
            $table->string('color')->after('year')->nullable();
        });

        Vehicle::query()->update(['color' => colority()->random()->toHex()->getValueColor()]);

        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('color')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
