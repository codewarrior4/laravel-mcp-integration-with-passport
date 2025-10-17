<?php

return [
    'auth' => [
        'client_id' => env('MCP_CLIENT_ID'),
        'client_secret' => env('MCP_CLIENT_SECRET'),
        'redirect_uri' => env('MCP_REDIRECT_URI'),
        'authorization_endpoint' => env('MCP_AUTHORIZATION_ENDPOINT'),
        'token_endpoint' => env('MCP_TOKEN_ENDPOINT'),
    ],
];
