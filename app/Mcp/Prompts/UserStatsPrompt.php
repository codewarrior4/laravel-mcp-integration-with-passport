<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

class UserStatsPrompt extends Prompt
{
    protected string $name = 'user_statistics';
    protected string $title = 'User Statistics Analysis';
    protected string $description = 'Get and interpret user transaction statistics';

    public function arguments(): array
    {
        return [
            new Argument(
                name: 'user_id',
                description: 'UUID of the user to analyze',
                required: true
            ),
        ];
    }

    public function handle(Request $request): array
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        $userId = $request->string('user_id');

        $systemMessage = "You are a data analyst specializing in user behavior analysis. Interpret user statistics and provide meaningful insights.";

        $userMessage = "Get statistics for user {$userId} using the get_user_stats tool. Analyze the data and provide insights about their transaction behavior, success rates, spending patterns, and overall financial activity. Highlight any concerning trends or positive patterns.";

        return [
            Response::text($systemMessage)->asAssistant(),
            Response::text($userMessage),
        ];
    }
}