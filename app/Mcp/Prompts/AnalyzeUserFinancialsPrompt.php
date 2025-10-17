<?php

namespace App\Mcp\Prompts;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

class AnalyzeUserFinancialsPrompt extends Prompt
{
    protected string $name = 'analyze_user_financials';
    protected string $title = 'Analyze User Financial Activity';
    protected string $description = 'Comprehensive financial analysis of a user';

    /**
     * Get the prompt's arguments and messages.
     */
    public function arguments():array{
        return [
            new Argument(
                    name: 'user_id',
                    description: 'UUID of the user to analyze',
                    required: true
            ),
        ];
    }



    public function schema(JsonSchema $schema): array
    {
        return [
            'user_id' => $schema->string()->description('UUID of the user to analyze'),
        ];
    }

    /**
     * Handle the prompt request.
     */
    public function handle(Request $request): array
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        $userId = $request->string('user_id');

        $systemMessage = "You are a financial analyst. Perform comprehensive analysis of user {$userId}'s financial activity using available MCP tools.";

        $userMessage = "Analyze user {$userId}'s financial data. Use get_user_balance, get_user_stats, and search_transactions tools to gather data, then provide a structured report with executive summary, key metrics, risk analysis, and recommendations.";

        return [
            Response::text($systemMessage)->asAssistant(),
            Response::text($userMessage),
        ];
    }


}
