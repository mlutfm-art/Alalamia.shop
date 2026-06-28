<?php
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        $rows = [
            'banner_enabled'     => '0',
            'banner_title'       => '',
            'banner_description' => '',
            'banner_image'       => '',
            'banner_button_text' => '',
            'banner_match_id'    => '',
        ];
        foreach ($rows as $key => $value) {
            DB::table('prediction_settings')->insertOrIgnore([
                'key'        => $key,
                'value'      => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void {
        DB::table('prediction_settings')->whereIn('key', [
            'banner_enabled',
            'banner_title',
            'banner_description',
            'banner_image',
            'banner_button_text',
            'banner_match_id',
        ])->delete();
    }
};
