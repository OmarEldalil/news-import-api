<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    const TITLE_MAX_LENGTH = 2048;
    const URL_MAX_LENGTH = 2048;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('title', self::TITLE_MAX_LENGTH);
            $table->text('content');
            $table->string('url', self::URL_MAX_LENGTH)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
