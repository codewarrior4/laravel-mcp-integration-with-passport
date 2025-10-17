<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MCPDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'country' => 'US',
                'phone' => '+1234567890',
                'password' => bcrypt('password'),
                'balance' => 1500.75,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'country' => 'CA',
                'phone' => '+1987654321',
                'password' => bcrypt('password'),
                'balance' => 2250.00,
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'country' => 'GB',
                'phone' => '+44123456789',
                'password' => bcrypt('password'),
                'balance' => 875.50,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create($userData);

            // Create transactions for each user
            $this->createTransactionsForUser($user);
        }
    }

    private function createTransactionsForUser(User $user): void
    {
        $transactions = [
            [
                'type' => 'FUND_WALLET',
                'amount' => 1000.00,
                'currency' => 'USD',
                'status' => 'SUCCESSFUL',
                'description' => 'Initial wallet funding',
                'recipient_name' => null,
                'recipient_email' => null,
            ],
            [
                'type' => 'SEND_MONEY',
                'amount' => 250.50,
                'currency' => 'USD',
                'status' => 'SUCCESSFUL',
                'description' => 'Money transfer to family',
                'recipient_name' => 'Alice Johnson',
                'recipient_email' => 'alice@example.com',
            ],
            [
                'type' => 'SEND_MONEY',
                'amount' => 100.00,
                'currency' => 'EUR',
                'status' => 'PENDING',
                'description' => 'Payment for services',
                'recipient_name' => 'Service Provider',
                'recipient_email' => 'provider@example.com',
            ],
            [
                'type' => 'WITHDRAW',
                'amount' => 75.25,
                'currency' => 'USD',
                'status' => 'FAILED',
                'description' => 'ATM withdrawal',
                'recipient_name' => null,
                'recipient_email' => null,
            ],
        ];

        foreach ($transactions as $transactionData) {
            Transaction::create([
                'user_id' => $user->id,
                'reference' => 'TXN-' . strtoupper(Str::random(8)),
                ...$transactionData,
            ]);
        }
    }
}