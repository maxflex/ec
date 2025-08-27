<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('client_parents', 'representatives');
    }

    public function down(): void
    {
        Schema::dropIfExists('representatives');
    }
};
