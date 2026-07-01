<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('penugasans', function (Blueprint $table) {
            // Drop foreign key first, then drop the column
            $table->dropForeign(['reviewer_id']);
            $table->dropColumn('reviewer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penugasans', function (Blueprint $table) {
            $table->unsignedBigInteger('reviewer_id')->nullable()->after('user_id');
            $table->foreign('reviewer_id')->references('id')->on('reviewers')->onDelete('set null');
        });
    }
};
