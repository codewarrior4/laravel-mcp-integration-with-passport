<?php

use App\Mcp\Servers\AdminServer;
use App\Mcp\Servers\WarriorServer;
use Laravel\Mcp\Facades\Mcp;

// OAuth discovery and client registration routes
Mcp::oauthRoutes();

// Public MCP server (no auth)
Mcp::web('/mcp/warrior', WarriorServer::class);

// Admin MCP server (OAuth protected)
Mcp::web('/mcp/admin', AdminServer::class)
    ->middleware('auth:api')
    ->name('mcp.admin');
