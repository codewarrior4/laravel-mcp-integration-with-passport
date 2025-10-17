<?php

namespace App\Mcp\Tools;

use App\Models\Transaction;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SearchTransactionsTool extends Tool
{
    protected string $name = 'search_transactions';
    protected string $title = 'Search Transactions';
    protected string $description = 'Find transactions by type, status, or currency';

    public function handle(Request $request): Response
    {
        $request->validate([
            'type' => 'sometimes|in:SEND_MONEY,FUND_WALLET,WITHDRAW',
            'status' => 'sometimes|in:PENDING,SUCCESSFUL,FAILED',
            'currency' => 'sometimes|string|size:3',
        ]);

        $query = Transaction::with('user');

        if ($request->has('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->has('currency')) {
            $query->where('currency', $request->string('currency'));
        }

        $transactions = $query->limit(10)->get();

        $result = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'user' => $transaction->user->name,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'status' => $transaction->status,
                'description' => $transaction->description,
            ];
        });

        return Response::text(json_encode($result, JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->description('Transaction type (SEND_MONEY, FUND_WALLET, WITHDRAW)'),
            'status' => $schema->string()->description('Transaction status (PENDING, SUCCESSFUL, FAILED)'),
            'currency' => $schema->string()->description('Currency code (USD, EUR, CAD)'),
        ];
    }
}