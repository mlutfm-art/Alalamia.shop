<?php

return [
    'credentials_file'    => storage_path('app/smartads-service-account.json'),
    'api_key'             => env('FCM_API_KEY',        'AIzaSyAdpOWF9Au5C7qMdXAMbxx0nkBbRoaNztE'),
    'auth_domain'         => env('FCM_AUTH_DOMAIN',    'alalamia-412d4.firebaseapp.com'),
    'project_id'          => env('FCM_PROJECT_ID',     'alalamia-412d4'),
    'storage_bucket'      => env('FCM_STORAGE_BUCKET', 'alalamia-412d4.firebasestorage.app'),
    'messaging_sender_id' => env('FCM_SENDER_ID',      '771867184256'),
    'app_id'              => env('FCM_APP_ID',          '1:771867184256:web:79473a95ec5f272643aa65'),
    'measurement_id'      => env('FCM_MEASUREMENT_ID', 'G-D2914PNSED'),
    'vapid_key'           => env('FCM_VAPID_KEY',      'BCT0cdCEi3N-IsvCd7AMVgHMSlN4IT0Qx1TUWCVqpboYl4tkr8tlzqW22csaD8RWYz_YX-htA7ees_IVvYYw8W0'),
];
