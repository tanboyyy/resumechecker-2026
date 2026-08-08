<?php

return [

    'plans' => [

        'free' => [
            'name' => 'Free',
            'price' => 0,
            'stripe_price_id' => env('STRIPE_PRICE_FREE'),
            'features' => [
                '3 analyses per month',
                'Basic ATS scoring',
                'Standard support',
            ],
            'limits' => [
                'analyses_per_month' => 3,
                'max_resume_size' => 5 * 1024 * 1024, // 5MB
                'analysis_types' => ['ats'],
                'export_reports' => false,
                'priority_processing' => false,
            ],
        ],

        'pro' => [
            'name' => 'Pro',
            'price' => 1900, // $19.00 in cents
            'stripe_price_id' => env('STRIPE_PRICE_PRO'),
            'features' => [
                '50 analyses per month',
                'All analysis types',
                'PDF report export',
                'Priority processing',
            ],
            'limits' => [
                'analyses_per_month' => 50,
                'max_resume_size' => 10 * 1024 * 1024, // 10MB
                'analysis_types' => ['ats', 'content', 'formatting', 'comparison'],
                'export_reports' => true,
                'priority_processing' => true,
            ],
        ],

        'enterprise' => [
            'name' => 'Enterprise',
            'price' => 4900, // $49.00 in cents
            'stripe_price_id' => env('STRIPE_PRICE_ENTERPRISE'),
            'features' => [
                'Unlimited analyses',
                'All analysis types',
                'PDF report export',
                'Priority processing',
                'API access',
                'Dedicated support',
            ],
            'limits' => [
                'analyses_per_month' => -1, // unlimited
                'max_resume_size' => 20 * 1024 * 1024, // 20MB
                'analysis_types' => ['ats', 'content', 'formatting', 'comparison'],
                'export_reports' => true,
                'priority_processing' => true,
                'api_access' => true,
            ],
        ],

    ],

    'default' => 'free',

];
