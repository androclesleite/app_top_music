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
        Schema::create('song_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('youtube_url');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('suggested_by')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['status']);
            $table->index(['reviewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('song_suggestions');
    }
};
