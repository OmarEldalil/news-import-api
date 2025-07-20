<?php

use App\Constants\ImportRequests;
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
        Schema::create('import_requests', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('original_file_name');
            $table->enum('status', ImportRequests::STATUS_LABELS)->default(ImportRequests::NEW);
            $table->timestamp('created_at')->useCurrent();
            $table->string('error_report_path')->nullable();
            $table->timestamp('processed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_requests');
    }
};
