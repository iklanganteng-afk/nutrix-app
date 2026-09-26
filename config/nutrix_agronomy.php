<?php

return [
    'version' => '1.0.0',
    'ruleset' => 'id-heuristic-v1',
    'weights' => [
        'moisture' => 0.35,
        'ph' => 0.25,
        'ec' => 0.25,
        'temperature' => 0.15,
    ],
    'targets' => [
        'corn' => ['ph' => [5.8, 7.0], 'temperature' => [18.0, 30.0], 'ec' => 1.7],
        'greenhouse' => ['ph' => [6.0, 6.8], 'temperature' => [18.0, 26.0], 'ec' => 2.5],
        'rice' => ['ph' => [5.5, 7.0], 'temperature' => [25.0, 32.0], 'ec' => 3.0],
        'custom' => ['ph' => [5.5, 7.0], 'temperature' => [18.0, 30.0], 'ec' => 1.7],
    ],
];
