<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * توسيع جدول smart_ads لدعم:
 * - إعلانات الفيديو (video)
 * - اختبار A/B (parent_id)
 * - التحليلات (impressions)
 * - الاستهداف (target_category_id, target_region, device_type)
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('smart_ads', function (Blueprint $table) {
            if (!Schema::hasColumn('smart_ads', 'video')) {
                $table->string('video')->nullable()->after('image');
            }
            if (!Schema::hasColumn('smart_ads', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('placement');
                $table->index('parent_id');
            }
            if (!Schema::hasColumn('smart_ads', 'ab_variant')) {
                $table->string('ab_variant', 8)->nullable()->after('parent_id'); // 'A' | 'B'
            }
            if (!Schema::hasColumn('smart_ads', 'impressions')) {
                $table->unsignedBigInteger('impressions')->default(0)->after('clicks');
            }
            if (!Schema::hasColumn('smart_ads', 'target_category_id')) {
                $table->unsignedBigInteger('target_category_id')->nullable()->after('impressions');
            }
            if (!Schema::hasColumn('smart_ads', 'target_region')) {
                $table->string('target_region')->nullable()->after('target_category_id');
            }
            if (!Schema::hasColumn('smart_ads', 'device_type')) {
                $table->string('device_type')->default('all')->after('target_region'); // all, android, ios, web
            }
        });
    }

    public function down()
    {
        Schema::table('smart_ads', function (Blueprint $table) {
            foreach (['video','parent_id','ab_variant','impressions','target_category_id','target_region','device_type'] as $col) {
                if (Schema::hasColumn('smart_ads', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
