<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hidata', function (Blueprint $table) {
            $table->string('user', 100);
            $table->string('no', 100);
            $table->decimal('battery_voltage', $precision = 5, $scale = 2);
            $table->decimal('battery_current', $precision = 5, $scale = 2);
            $table->decimal('battery_charge_power', $precision = 5, $scale = 2);
            $table->decimal('battery_soc', $precision = 5, $scale = 2);
            $table->decimal('battery_max_charge_current', $precision = 5, $scale = 2);
            $table->decimal('pv_voltage', $precision = 5, $scale = 2);
            $table->decimal('pv_current', $precision = 5, $scale = 2);
            $table->decimal('pv_power', $precision = 5, $scale = 2);
            $table->decimal('pv_battery_charge_current', $precision = 5, $scale = 2);
            $table->decimal('grid_voltage', $precision = 5, $scale = 2);
            $table->decimal('grid_input_current', $precision = 5, $scale = 2);
            $table->decimal('grid_battery_charge_current', $precision = 5, $scale = 2);
            $table->decimal('grid_frequency', $precision = 5, $scale = 2);
            $table->decimal('grid_battery_charge_max_current', $precision = 5, $scale = 2);
            $table->decimal('inverter_voltage', $precision = 5, $scale = 2);
            $table->decimal('inverter_current', $precision = 5, $scale = 2);
            $table->decimal('inverter_frequency', $precision = 5, $scale = 2);
            $table->decimal('inverter_power', $precision = 5, $scale = 2);
            $table->decimal('inverter_output_priority', $precision = 5, $scale = 2);
            $table->decimal('inverter_charger_priority', $precision = 5, $scale = 2);
            $table->decimal('temp_dc', $precision = 5, $scale = 2);
            $table->decimal('temp_ac', $precision = 5, $scale = 2);
            $table->decimal('temp_tr', $precision = 5, $scale = 2);
            $table->dateTime('create_at');
            $table->primary(['user', 'no', 'create_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tables');
    }
};
