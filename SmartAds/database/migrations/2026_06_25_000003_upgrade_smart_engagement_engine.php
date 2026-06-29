<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // إضافة الحقول للموديل الأساسي إذا لم تكن موجودة
        Schema::table('smart_ads', function (Blueprint $table) {
            if (!Schema::hasColumn('smart_ads', 'action_type')) {
                $table->string('action_type')->nullable()->after('ad_type');
            }
        });

        // جدول تتبع النقرات التفصيلي
        Schema::create('smart_ad_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smart_ad_id')->constrained('smart_ads')->onDelete('cascade');
            $table->foreignId('user_id')->nullable();
            $table->string('action_triggered');
            $table->string('platform')->nullable(); // android, ios, web
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('smart_ad_clicks');
    }
};
