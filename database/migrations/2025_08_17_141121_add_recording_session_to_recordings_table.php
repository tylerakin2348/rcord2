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
        Schema::table('recordings', function (Blueprint $table) {
            $table->unsignedBigInteger('recording_session_id')->nullable()->after('recording_type_id');
            $table->integer('loop_number')->default(1)->after('recording_session_id'); // Which loop in the session
            
            // Foreign key constraint
            $table->foreign('recording_session_id')
                  ->references('id')
                  ->on('recording_sessions')
                  ->onDelete('cascade');
            
            // Index for performance
            $table->index('recording_session_id');
            $table->index(['recording_session_id', 'loop_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recordings', function (Blueprint $table) {
            $table->dropForeign(['recording_session_id']);
            $table->dropColumn(['recording_session_id', 'loop_number']);
        });
    }
};
