<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | League Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration settings for the Premier League
    | simulation system.
    |
    */

    'teams' => [
        'required_count' => 4,
        'default_teams' => [
            'Arsenal',
            'Chelsea', 
            'Liverpool',
            'Manchester City',
        ],
        'strength' => [
            'min' => 0.1,
            'max' => 5.0,
            'default_attack' => 2.5,
            'default_defense' => 2.5,
        ],
    ],

    'season' => [
        'total_weeks' => 6,
        'matches_per_week' => 2,
        'status' => [
            'active' => 'active',
            'completed' => 'completed',
            'paused' => 'paused',
        ],
    ],

    'simulation' => [
        'home_advantage' => 1.2,
        'max_goals_per_match' => 20,
        'min_possession' => 25,
        'max_possession' => 75,
        'default_shots_range' => [5, 20],
        'shots_on_target_ratio' => [0.3, 0.5],
    ],

    'predictions' => [
        'cache_ttl' => 300, // 5 minutes
        'history_limit' => 20,
        'min_weeks_for_prediction' => 1,
        'confidence_threshold' => 0.6,
    ],

    'api' => [
        'pagination' => [
            'default_per_page' => 15,
            'max_per_page' => 100,
        ],
        'cache' => [
            'ttl' => 300, // 5 minutes
            'enabled' => true,
        ],
    ],
];