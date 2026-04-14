<?php

use App\Models\VehicleServiceReminder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicle_services', function (Blueprint $table) {
            $table->foreignUuid('vehicle_service_reminder_id')->nullable()->constrained('vehicle_service_reminders')->nullOnDelete()->after('vehicle_service_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_services', function (Blueprint $table) {
            $table->dropForeignIdFor(VehicleServiceReminder::class);
            $table->dropColumn('vehicle_service_reminder_id');
        });
    }
};
