<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class TransactionLimitsResource extends Resource
{
    protected string $name = 'transaction_limits';
    protected string $title = 'Transaction Limits & Rules';
    protected string $description = 'Transaction limits and business rules for different regions and currencies';
    protected string $uri = 'finance://limits';

    public function handle(Request $request): Response
    {
        $limits = [
            'daily_limits' => [
                'SEND_MONEY' => '$5000 USD equivalent',
                'FUND_WALLET' => '$10000 USD equivalent',
                'WITHDRAW' => '$3000 USD equivalent'
            ],
            'currency_support' => [
                'USD' => ['min' => 1, 'max' => 50000],
                'EUR' => ['min' => 1, 'max' => 45000],
                'CAD' => ['min' => 1, 'max' => 65000],
                'NGN' => ['min' => 500, 'max' => 20000000]
            ],
            'regional_rules' => [
                'US' => 'KYC required for transactions > $3000',
                'CA' => 'Enhanced verification for > $5000 CAD',
                'GB' => 'FCA compliance required for > £2500',
                'NG' => 'CBN regulations apply for > ₦1,000,000'
            ],
            'business_hours' => [
                'standard' => '24/7 for amounts < $1000',
                'review_required' => 'Business hours only for amounts > $10000',
                'instant' => 'Immediate processing for verified users'
            ]
        ];

        return Response::text(json_encode($limits, JSON_PRETTY_PRINT));
    }
}