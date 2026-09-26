<?php

return [
    'soils' => [
        'unspecified' => [
            'aliases' => [],
            'theta_fc' => 30.0,
            'theta_pwp' => 13.0,
            'theta_sat' => 45.0,
            'confidence' => 0.85,
        ],
        'pasir' => [
            'aliases' => ['sand'],
            'theta_fc' => 12.0,
            'theta_pwp' => 5.0,
            'theta_sat' => 35.0,
            'confidence' => 1.0,
        ],
        'liat_berpasir' => [
            'aliases' => ['sandy_loam'],
            'theta_fc' => 20.0,
            'theta_pwp' => 9.0,
            'theta_sat' => 42.0,
            'confidence' => 1.0,
        ],
        'latosol' => [
            'aliases' => ['loam_generic', 'vulkanis'],
            'theta_fc' => 30.0,
            'theta_pwp' => 13.0,
            'theta_sat' => 45.0,
            'confidence' => 1.0,
        ],
        'liat' => [
            'aliases' => ['clay'],
            'theta_fc' => 42.0,
            'theta_pwp' => 28.0,
            'theta_sat' => 55.0,
            'confidence' => 1.0,
        ],
        'organosol' => [
            'aliases' => ['gambut'],
            'theta_fc' => 50.0,
            'theta_pwp' => 20.0,
            'theta_sat' => 75.0,
            'confidence' => 0.85,
        ],
    ],
    'metrics' => ['moisture', 'ph', 'temperature', 'ec'],
    'sensor_models' => [
        'moisture' => ['SEN0193', 'YL-69', 'Capacitive-1'],
        'temperature' => ['DHT22', 'DS18B20', 'LM35'],
        'ph' => ['PH-4502C', 'Atlas-pH', 'PH-1'],
        'ec' => ['DFRobot-EC', 'DFRobot EC', 'Atlas-EC', 'TDS-V1'],
    ],
    'boards' => ['esp32', 'esp8266', 'arduino'],
];
