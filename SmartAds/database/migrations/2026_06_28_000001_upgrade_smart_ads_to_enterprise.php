<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('smart_ads', function (Blueprint $table) {
            // Enterprise UI & Content
            if (!Schema::hasColumn('smart_ads', 'sub_title')) {
                $table->string('sub_title')->nullable()->after('title');
            }
            if (!Schema::hasColumn('smart_ads', 'button_text')) {
                $table->string('button_text')->nullable()->after('sub_title');
            }
            
            // Firebase Dynamic Engine
            if (!Schema::hasColumn('smart_ads', 'firebase_payload')) {
                $table->json('firebase_payload')->nullable()->after('action_data');
            }
            if (!Schema::hasColumn('smart_ads', 'dynamic_context')) {
                $table->json('dynamic_context')->nullable()->after('firebase_payload');
            }
            
            // Targeting & Smart Engine
            if (!Schema::hasColumn('smart_ads', 'targeting_config')) {
                $table->json('targeting_config')->nullable();
            }
            if (!Schema::hasColumn('smart_ads', 'triggers_config')) {
                $table->json('triggers_config')->nullable();
            }
            if (!Schema::hasColumn('smart_ads', 'scheduling_config')) {
                $table->json('scheduling_config')->nullable();
            }
            
            // Stats
            if (!Schema::hasColumn('smart_ads', 'sent_count')) {
                $table->integer('sent_count')->default(0);
            }
            if (!Schema::hasColumn('smart_ads', 'delivered_count')) {
                $table->integer('delivered_count')->default(0);
            }
            if (!Schema::hasColumn('smart_ads', 'opened_count')) {
                $table->integer('opened_count')->default(0);
            }
            if (!Schema::hasColumn('smart_ads', 'conversion_count')) {
                $table->integer('conversion_count')->default(0);
            }
            
            // AI Flag
            if (!Schema::hasColumn('smart_ads', 'is_ai_generated')) {
                $table->boolean('is_ai_generated')->default(false);
            }
        });
    }

    public function down()
    {
        Schema::table('smart_ads', function (Blueprint $table) {
            $table->dropColumn([
                'sub_title', 'button_text', 'firebase_payload', 'dynamic_context',
                'targeting_config', 'triggers_config', 'scheduling_config',
                'sent_count', 'delivered_count', 'opened_count', 'conversion_count',
                'is_ai_generated'
            ]);
        });
    }
};
