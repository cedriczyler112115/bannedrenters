<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banned_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banned_id')->constrained('banned')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('action', 50);
            $table->string('field', 50);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['banned_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banned_audit_trails');
    }
};
