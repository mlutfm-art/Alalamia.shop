<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * جدول smart_ad_notifications
 * ─────────────────────────────
 * يُخزّن كل إعلان/action كسجل إشعار، ويستخدم:
 *   - Push Notification (display_type = push)
 *   - In-App Banner (display_type = inapp_banner)
 *   - Notification Center (display_type = notification_center)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smart_ad_notifications', function (Blueprint $table) {
            $table->id();

            // الإعلان المصدر
            $table->unsignedBigInteger('smart_ad_id');
            $table->foreign('smart_ad_id')->references('id')->on('smart_ads')->onDelete('cascade');

            // المستخدم المستهدف (null = للجميع)
            $table->unsignedBigInteger('user_id')->nullable()->index();

            // محتوى الإشعار
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('image_url')->nullable();

            // الإجراء المرتبط
            $table->string('action_type')->index();

            // الـ payload الكامل لـ Flutter (JSON)
            $table->json('flutter_payload');

            // نوع العرض: push | inapp_banner | notification_center
            $table->string('display_type')->default('notification_center')->index();

            // حالة القراءة
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();

            // انتهاء الصلاحية (مزامنة مع الإعلان)
            $table->timestamp('expires_at')->nullable()->index();

            $table->timestamps();

            // فهرس مركب للاستعلامات الشائعة (user_id + is_read + display_type)
            $table->index(['user_id', 'is_read', 'display_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smart_ad_notifications');
    }
};
