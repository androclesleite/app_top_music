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
        Schema::table('song_suggestions', function (Blueprint $table) {
            $table->string('artist')->nullable()->after('title');
            $table->string('suggested_by_name')->nullable()->after('suggested_by');
            $table->string('suggested_by_email')->nullable()->after('suggested_by_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('song_suggestions', function (Blueprint $table) {
            $table->dropColumn(['artist', 'suggested_by_name', 'suggested_by_email']);
        });
    }
};
