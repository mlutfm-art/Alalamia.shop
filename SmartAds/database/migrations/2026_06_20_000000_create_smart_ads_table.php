<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('smart_ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image')->nullable();
            $table->string('ad_type'); // banner, popup, notification, video, native
            $table->string('placement'); // home, category, product, ...
            $table->json('action_data')->nullable();
            $table->boolean('status')->default(1);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('smart_ads');
    }
};
