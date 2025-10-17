<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetUserBalanceTool;
use App\Mcp\Tools\SearchTransactionsTool;
use App\Mcp\Tools\GetUserStatsTool;
use App\Mcp\Prompts\AnalyzeUserFinancialsPrompt;
use App\Mcp\Resources\FinancialGuidelinesResource;
use Laravel\Mcp\Server;

class AdminServer extends Server
{
    protected string $name = 'Admin Financial Server';
    protected string $version = '1.0.0';
    protected string $description = 'Administrative access to financial data with OAuth authentication';

    protected string $instructions = <<<'MARKDOWN'
        This server provides administrative access to financial data.
        Requires OAuth authentication via Laravel Passport.
        
        Available capabilities:
        - User balance inquiries
        - Transaction searches and analysis
        - Financial health assessments
        - Risk analysis based on business guidelines
    MARKDOWN;

    protected array $tools = [
        GetUserBalanceTool::class,
        SearchTransactionsTool::class,
        GetUserStatsTool::class,
    ];

    protected array $resources = [
        FinancialGuidelinesResource::class,
    ];

    protected array $prompts = [
        AnalyzeUserFinancialsPrompt::class,
    ];
}