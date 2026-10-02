<?php

return [
    'device_key' => env('NUTRIX_IOT_DEVICE_KEY'),
    'moisture_alert_below' => (float) env('NUTRIX_MOISTURE_ALERT_BELOW', 20),
    'auto_water_below' => (float) env('NUTRIX_AUTO_WATER_BELOW', 30),
    'auto_water_cooldown_minutes' => (int) env('NUTRIX_AUTO_WATER_COOLDOWN_MINUTES', 30),
    'auto_water_duration_sec' => (int) env('NUTRIX_AUTO_WATER_DURATION_SEC', 10),
];
