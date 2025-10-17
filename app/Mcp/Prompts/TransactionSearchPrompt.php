<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

class TransactionSearchPrompt extends Prompt
{
    protected string $name = 'search_transactions';
    protected string $title = 'Search and Analyze Transactions';
    protected string $description = 'Search for transactions by criteria and provide insights';

    public function arguments(): array
    {
        return [
            new Argument(
                name: 'type',
                description: 'Transaction type (SEND_MONEY, FUND_WALLET, WITHDRAW)',
                required: false
            ),
            new Argument(
                name: 'status',
                description: 'Transaction status (PENDING, SUCCESSFUL, FAILED)',
                required: false
            ),
            new Argument(
                name: 'currency',
                description: 'Currency code (USD, EUR, CAD)',
                required: false
            ),
        ];
    }

    public function handle(Request $request): array
    {
        $request->validate([
            'type' => 'sometimes|in:SEND_MONEY,FUND_WALLET,WITHDRAW',
            'status' => 'sometimes|in:PENDING,SUCCESSFUL,FAILED',
            'currency' => 'sometimes|string|size:3',
        ]);

        $criteria = array_filter([
            'type' => $request->string('type'),
            'status' => $request->string('status'),
            'currency' => $request->string('currency'),
        ]);
        $criteriaText = empty($criteria) ? 'all transactions' : 'transactions matching: ' . json_encode($criteria);

        $systemMessage = "You are a transaction analyst. Search and analyze transaction patterns based on the given criteria.";

        $userMessage = "Search for {$criteriaText} using the search_transactions tool. Analyze the results and provide insights about patterns, trends, and any notable findings in the transaction data.";

        return [
            Response::text($systemMessage)->asAssistant(),
            Response::text($userMessage),
        ];
    }
}