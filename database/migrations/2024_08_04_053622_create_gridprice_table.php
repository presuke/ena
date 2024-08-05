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
        Schema::create('gridprice', function (Blueprint $table) {
            $table->string('date', 10);
            $table->integer('kbn')->length(1);
            $table->integer('timezone')->length(1);
            $table->decimal('price', $precision = 5, $scale = 2);
            $table->primary(['date', 'kbn', 'timezone']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gridprice');
    }
};
