<?php
/**
 * StormHosting API v1
 * API Documentation and Information
 */

header('Content-Type: application/json');

$response = [
    'name' => 'StormHosting API',
    'version' => '1.0',
    'status' => 'active',
    'endpoints' => [
        [
            'path' => '/v1/site-check',
            'method' => 'POST',
            'description' => 'Check website availability and performance',
            'authentication' => 'Bearer token required',
            'rate_limit' => '1000 requests per hour'
        ]
    ],
    'documentation' => 'https://sthost.pro/pages/tools/site-check.php#api',
    'support' => 'https://sthost.pro/pages/contacts.php',
];

echo json_encode($response, JSON_PRETTY_PRINT);
