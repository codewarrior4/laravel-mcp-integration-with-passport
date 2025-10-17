<?php

namespace App\Mcp\Tools;

use App\Models\User;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GetUserStatsTool extends Tool
{
    protected string $name = 'get_user_stats';
    protected string $title = 'Get User Statistics';
    protected string $description = 'Calculate user transaction statistics';

    public function handle(Request $request): Response
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        $user = User::with('transactions')->find($request->string('user_id'));

        $stats = [
            'user_name' => $user->name,
            'balance' => $user->balance,
            'total_transactions' => $user->transactions->count(),
            'successful_transactions' => $user->transactions->where('status', 'SUCCESSFUL')->count(),
            'pending_transactions' => $user->transactions->where('status', 'PENDING')->count(),
            'failed_transactions' => $user->transactions->where('status', 'FAILED')->count(),
            'total_sent' => $user->transactions->whereIn('type', ['SEND_MONEY', 'WITHDRAW'])->sum('amount'),
            'total_received' => $user->transactions->where('type', 'FUND_WALLET')->sum('amount'),
        ];

        return Response::text(json_encode($stats, JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'user_id' => $schema->string()->description('The UUID of the user'),
        ];
    }
}