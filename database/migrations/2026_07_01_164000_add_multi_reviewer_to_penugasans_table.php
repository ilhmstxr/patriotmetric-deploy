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
            $table->unsignedBigInteger('reviewer_1_id')->nullable()->after('reviewer_id');
            $table->foreign('reviewer_1_id')->references('id')->on('reviewers')->onDelete('set null');

            $table->unsignedBigInteger('reviewer_2_id')->nullable()->after('reviewer_1_id');
            $table->foreign('reviewer_2_id')->references('id')->on('reviewers')->onDelete('set null');

            $table->unsignedBigInteger('reviewer_3_id')->nullable()->after('reviewer_2_id');
            $table->foreign('reviewer_3_id')->references('id')->on('reviewers')->onDelete('set null');

            $table->decimal('nilai_reviewer_1', 8, 2)->default(0)->after('reviewer_3_id');
            $table->decimal('nilai_reviewer_2', 8, 2)->default(0)->after('nilai_reviewer_1');
            $table->decimal('nilai_reviewer_3', 8, 2)->default(0)->after('nilai_reviewer_2');
            $table->decimal('nilai_rata_rata', 8, 2)->default(0)->after('nilai_reviewer_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penugasans', function (Blueprint $table) {
            $table->dropForeign(['reviewer_1_id']);
            $table->dropForeign(['reviewer_2_id']);
            $table->dropForeign(['reviewer_3_id']);
            $table->dropColumn([
                'reviewer_1_id',
                'reviewer_2_id',
                'reviewer_3_id',
                'nilai_reviewer_1',
                'nilai_reviewer_2',
                'nilai_reviewer_3',
                'nilai_rata_rata'
            ]);
        });
    }
};
