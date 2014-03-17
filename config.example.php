<?php
return [
    'lookback' => 10, // number of minutes to look back
    'threshold' => 2, // number of points above warn or alert before, do anything

    'graphite' => [
        'url' => 'http://graphite.example.com',
        // hardcoded for digest auth at the moment, see src/GraphiteAlert/GraphiteData.php
        'user' => 'user',
        'pass' => 'password',
    ],

    'email' => [
        'from' => 'noreply@example.com',
        'to' => 'noreply@example.com',
        'subject' => '{action}: {name} has reached {type}.',
        'warn' => [
            'trigger' => "Metric {name} has reached its warning threshold.\n{times} times in the last {lookback} minutes, last bad {value}\n\n{url}",
            'resolve' => "Metric {name} has recovered.\n\n{url}"
        ],
        'alert' => [
            'trigger' => "Metric {name} has reached its alert threshold!\n{times} times in the last {lookback} minutes, last bad {value}\n\n{url}",
            'resolve' => "Metric {name} has recovered.\n\n{url}"
        ]
    ],
    
    'pagerduty' => [
        'key' => 'pagerdutyapikey',
        'warn' => false,
        'alert' => [
            'description' => "Metric {name} has reached its alert threshold! {value}"
            ],
        'alert' => false,
    ],

    'metrics' => [
        'metric1' => [
            'target' => "derivative(x.y.server.bytes_sent)",
            'warn' => '1200M',
            'alert' => '1500M',
            'unit' => 'BtoM',
        ],
    ]
];
