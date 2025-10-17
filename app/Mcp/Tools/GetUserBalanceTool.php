<?php

namespace App\Mcp\Tools;

use App\Models\User;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class GetUserBalanceTool extends Tool
{
    /**
     * The tool's name.
     */
    protected string $name = 'get_user_balance';

    /**
     * The tool's title.
     */
    protected string $title = 'Get User Balance';

    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Get user's total balance by calculating successful transactions.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        $user = User::find($request->string('user_id'));

        return Response::text("User {$user->name} has a balance of $" . number_format($user->balance, 2));
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'user_id' => $schema->string()->description('The UUID of the user'),
        ];
    }
}
