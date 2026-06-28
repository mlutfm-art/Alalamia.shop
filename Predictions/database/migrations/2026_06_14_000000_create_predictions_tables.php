<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('prediction_matches', function(Blueprint $t) {
            $t->id(); $t->string('title')->nullable();
            $t->string('team1_name'); $t->string('team1_logo')->nullable();
            $t->string('team2_name'); $t->string('team2_logo')->nullable();
            $t->dateTime('match_time'); $t->dateTime('prediction_close_time');
            $t->enum('status',['active','closed','completed'])->default('active');
            $t->integer('actual_team1')->nullable(); $t->integer('actual_team2')->nullable();
            $t->integer('reward_points')->default(100); $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('match_predictions', function(Blueprint $t) {
            $t->id();
            $t->foreignId('match_id')->constrained('prediction_matches')->cascadeOnDelete();
            $t->unsignedBigInteger('user_id');
            $t->integer('predicted_team1'); $t->integer('predicted_team2');
            $t->integer('points_awarded')->nullable(); $t->integer('distance_score')->nullable();
            $t->enum('prediction_status',['pending','evaluated'])->default('pending');
            $t->unique(['match_id','user_id']); $t->index(['user_id','prediction_status']); $t->index('match_id');
            $t->timestamps();
        });
        Schema::create('prediction_settings', function(Blueprint $t) {
            $t->id(); $t->string('key')->unique(); $t->text('value')->nullable(); $t->timestamps();
        });
        DB::table('prediction_settings')->insert([
            ['key'=>'enabled','value'=>'1','created_at'=>now(),'updated_at'=>now()],
            ['key'=>'default_reward_points','value'=>'100','created_at'=>now(),'updated_at'=>now()],
            ['key'=>'close_threshold','value'=>'2','created_at'=>now(),'updated_at'=>now()],
            ['key'=>'partial_reward_multiplier','value'=>'0.5','created_at'=>now(),'updated_at'=>now()],
        ]);
    }
    public function down(): void {
        Schema::dropIfExists('match_predictions');
        Schema::dropIfExists('prediction_matches');
        Schema::dropIfExists('prediction_settings');
    }
};
