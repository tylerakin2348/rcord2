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
        Schema::create('recording_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('recording_type_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('session_duration')->default(0); // Total duration across all loops in seconds
            $table->integer('total_loops')->default(0);
            $table->integer('loop_duration')->default(10); // Duration per loop in seconds
            $table->json('settings')->nullable(); // Additional settings like loop configuration
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('recording_type_id')->references('id')->on('recording_types')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes for performance
            $table->index('recording_type_id');
            $table->index('user_id');
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recording_sessions');
    }
};
