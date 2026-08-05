<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banned', function (Blueprint $table) {
            $table->id();
            $table->string('fullname', 150);
            $table->string('license');
            $table->text('description');
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('date_created')->useCurrent();

            $table->index('fullname');
            $table->index('date_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banned');
    }
};
