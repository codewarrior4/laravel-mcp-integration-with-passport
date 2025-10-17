<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetUserBalanceTool;
use App\Mcp\Tools\SearchTransactionsTool;
use App\Mcp\Tools\GetUserStatsTool;
use App\Mcp\Prompts\AnalyzeUserFinancialsPrompt;
use App\Mcp\Prompts\TransactionSearchPrompt;
use App\Mcp\Prompts\UserStatsPrompt;
use App\Mcp\Resources\FinancialGuidelinesResource;
use App\Mcp\Resources\TransactionLimitsResource;
use Laravel\Mcp\Server;

class WarriorServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'Warrior Server';


    /**
     * The MCP server's version.
     */
    protected string $version = '0.0.1';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = <<<'MARKDOWN'
        Instructions describing how to use the server and its features.
    MARKDOWN;

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        GetUserBalanceTool::class,
        SearchTransactionsTool::class,
        GetUserStatsTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        FinancialGuidelinesResource::class,
        TransactionLimitsResource::class,
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        AnalyzeUserFinancialsPrompt::class,
        TransactionSearchPrompt::class,
        UserStatsPrompt::class,
    ];
}
