<?php

use App\Enums\CallerType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            // Тип разговора (AI-классификация): клиент новый/старый, преподаватель или другое.
            $table->enum('caller_type', array_column(CallerType::cases(), 'value'))
                ->nullable()
                ->after('instruction')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn('caller_type');
        });
    }
};
