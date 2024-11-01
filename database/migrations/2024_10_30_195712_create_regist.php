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
        Schema::create('hi', function (Blueprint $table) {
            $table->id();
            $table->string('user', 100);
            $table->string('no', 100);
            $table->string('loooparea', 100);
            $table->string('ip', 100);
            $table->datetime('create_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hi');
    }
};
