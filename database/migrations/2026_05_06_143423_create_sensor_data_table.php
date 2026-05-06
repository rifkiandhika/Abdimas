<?php

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
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
 
            $table->string('device_id', 50)->index();
 
            $table->unsignedSmallInteger('flame_raw');      
            $table->boolean('fire');                        
 
            $table->decimal('voltage',    6, 2)->nullable(); 
            $table->decimal('current',    6, 2)->nullable(); 
            $table->decimal('power',      8, 1)->nullable();
            $table->decimal('energy',    10, 0)->nullable();
            $table->decimal('deviasi_pct', 6, 1)->nullable();
            $table->boolean('voltage_ok')->nullable();       
 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};
