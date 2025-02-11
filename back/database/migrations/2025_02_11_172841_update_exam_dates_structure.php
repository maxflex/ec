<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $data = \Illuminate\Support\Facades\DB::table('exam_dates')->get();
        foreach ($data as $datum) {
            $dates = collect(json_decode($datum->dates))->map(fn ($date) => [
                'date' => $date,
                'is_reserve' => 0,
            ]);
            \Illuminate\Support\Facades\DB::table('exam_dates')->whereId($datum->id)->update([
                'dates' => $dates->toJson(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
