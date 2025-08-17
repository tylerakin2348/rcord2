<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recording_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'single' or 'looped'
            $table->string('display_name'); // 'Single Recording' or 'Looped Recording'
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert the default recording types
        DB::table('recording_types')->insert([
            [
                'name' => 'single',
                'display_name' => 'Single Recording',
                'description' => 'A single continuous audio recording',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'looped',
                'display_name' => 'Looped Recording',
                'description' => 'A recording that can be looped multiple times',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recording_types');
    }
};
