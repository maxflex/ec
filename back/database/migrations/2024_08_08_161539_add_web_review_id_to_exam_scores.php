<?php

use App\Models\WebReview;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_scores', function (Blueprint $table) {
            $table->foreignIdFor(WebReview::class)->after('client_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_scores', function (Blueprint $table) {
            //
        });
    }
};
