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
        Schema::table('recordings', function (Blueprint $table) {
            // Add recording_type_id foreign key column
            $table->unsignedBigInteger('recording_type_id')->after('user_id')->nullable();
            
            // Add foreign key constraint
            $table->foreign('recording_type_id')
                  ->references('id')
                  ->on('recording_types')
                  ->onDelete('set null');
            
            // Add index for performance
            $table->index('recording_type_id');
        });

        // Migrate existing recordings to use recording types
        // First, get the recording type IDs
        $singleTypeId = DB::table('recording_types')->where('name', 'single')->value('id');
        $loopedTypeId = DB::table('recording_types')->where('name', 'looped')->value('id');

        // Update existing recordings based on their title patterns
        if ($singleTypeId) {
            DB::table('recordings')
                ->where('title', 'like', '%single%')
                ->orWhere('title', 'like', '%Single%')
                ->update(['recording_type_id' => $singleTypeId]);
        }

        if ($loopedTypeId) {
            DB::table('recordings')
                ->where('title', 'like', '%looped%')
                ->orWhere('title', 'like', '%Looped%')
                ->update(['recording_type_id' => $loopedTypeId]);
        }

        // Set any remaining recordings to single by default
        if ($singleTypeId) {
            DB::table('recordings')
                ->whereNull('recording_type_id')
                ->update(['recording_type_id' => $singleTypeId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recordings', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['recording_type_id']);
            
            // Drop the column
            $table->dropColumn('recording_type_id');
        });
    }
};
