<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('device_tokens')) {
            return;
        }

        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('tokenable');
            $table->string('token', 512)->unique();
            $table->string('device_type')->default('web');
            $table->string('browser')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index(['tokenable_id', 'tokenable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
