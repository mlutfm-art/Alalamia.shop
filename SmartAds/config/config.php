<?php
return [
    'name'           => 'SmartAds',
    'fcm_server_key' => env('FCM_SERVER_KEY', ''),
    'android_package'=> env('ANDROID_PACKAGE', ''),
    'ios_app_id'     => env('IOS_APP_ID', ''),
    'banner_duration_ms'  => env('SMARTADS_BANNER_DURATION', 5000),
    'default_push_topic'  => env('SMARTADS_DEFAULT_TOPIC', 'all_users'),
    'max_pending_banners' => 3,
    'ad_types'    => ['banner', 'popup', 'notification', 'video', 'native'],
    'device_types'=> ['all', 'android', 'ios', 'web'],
    'placements'  => [
        'home','category','product','cart','checkout',
        'profile','splash','search','deals','wallet',
    ],
];
