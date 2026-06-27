<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recording_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->foreignId('recording_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recording_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('format');
            $table->string('status')->default('pending');
            $table->string('file_path')->nullable();
            $table->string('download_filename')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recording_exports');
    }
};
