<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class FinancialGuidelinesResource extends Resource
{
    protected string $name = 'financial_guidelines';
    protected string $title = 'Financial Analysis Guidelines';
    protected string $description = 'Guidelines for analyzing user financial health and risk assessment';
    protected string $uri = 'finance://guidelines';

    public function handle(Request $request): Response
    {
        $guidelines = [
            'risk_assessment' => [
                'low_risk' => 'Balance > $1000, Success rate > 90%, Failed transactions < 5%',
                'medium_risk' => 'Balance $500-$1000, Success rate 70-90%, Failed transactions 5-15%',
                'high_risk' => 'Balance < $500, Success rate < 70%, Failed transactions > 15%'
            ],
            'transaction_patterns' => [
                'healthy' => 'Regular funding, moderate spending, low failure rate',
                'concerning' => 'Irregular funding, high withdrawal rate, frequent failures',
                'suspicious' => 'Large sudden transactions, unusual patterns, high failure rate'
            ],
            'balance_thresholds' => [
                'excellent' => '> $2000',
                'good' => '$1000 - $2000',
                'fair' => '$500 - $1000',
                'poor' => '< $500'
            ],
            'recommendations' => [
                'low_balance' => 'Encourage regular funding, set up automatic deposits',
                'high_failures' => 'Review transaction methods, verify account details',
                'irregular_activity' => 'Monitor for fraud, suggest spending limits'
            ]
        ];

        return Response::text(json_encode($guidelines, JSON_PRETTY_PRINT));
    }
}