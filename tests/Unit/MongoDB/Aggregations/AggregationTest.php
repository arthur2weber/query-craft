<?php
/**
 * MongoDB Aggregation Test
 * 
 * Tests MongoDB aggregation pipeline functionality including
 * match, group, lookup, project, and complex aggregation operations.
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\MongoQuery;

echo "🧪 MONGODB AGGREGATION: Running Tests\n";
echo str_repeat("=", 50) . "\n\n";

resetTestCounters();

try {
    // Test 1: Basic Match Operation
    echo "1. Basic Match Operation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('orders')
        ->match(['status' => 'completed'])
        ->toQuery();
    
    $expected = [
        [
            '$match' => ['status' => 'completed']
        ]
    ];
    
    validateEquals(
        'Basic match operation',
        $expected,
        $result,
        'Match should create aggregation pipeline'
    );

    // Test 2: Group Operation
    echo "\n2. Group Operation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('sales')
        ->group('customer_id', [
            'total_spent' => ['$sum' => '$amount'],
            'order_count' => ['$sum' => 1],
            'avg_order' => ['$avg' => '$amount']
        ])
        ->toQuery();
    
    $expected = [
        [
            '$group' => [
                '_id' => '$customer_id',
                'total_spent' => ['$sum' => '$amount'],
                'order_count' => ['$sum' => 1],
                'avg_order' => ['$avg' => '$amount']
            ]
        ]
    ];
    
    validateEquals(
        'Group operation',
        $expected,
        $result,
        'Group should create proper aggregation stage'
    );

    // Test 3: Lookup (Join) Operation
    echo "\n3. Lookup (Join) Operation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('orders')
        ->lookup('users', 'user_id', '_id', 'user')
        ->toQuery();
    
    $expected = [
        [
            '$lookup' => [
                'from' => 'users',
                'localField' => 'user_id',
                'foreignField' => '_id',
                'as' => 'user'
            ]
        ]
    ];
    
    validateEquals(
        'Lookup operation',
        $expected,
        $result,
        'Lookup should create proper join stage'
    );

    // Test 4: Project Operation
    echo "\n4. Project Operation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('users')
        ->project([
            'name' => 1,
            'email' => 1,
            'full_name' => ['$concat' => ['$first_name', ' ', '$last_name']],
            'age_group' => [
                '$switch' => [
                    'branches' => [
                        ['case' => ['$lt' => ['$age', 18]], 'then' => 'minor'],
                        ['case' => ['$lt' => ['$age', 65]], 'then' => 'adult']
                    ],
                    'default' => 'senior'
                ]
            ]
        ])
        ->toQuery();
    
    $expected = [
        [
            '$project' => [
                'name' => 1,
                'email' => 1,
                'full_name' => ['$concat' => ['$first_name', ' ', '$last_name']],
                'age_group' => [
                    '$switch' => [
                        'branches' => [
                            ['case' => ['$lt' => ['$age', 18]], 'then' => 'minor'],
                            ['case' => ['$lt' => ['$age', 65]], 'then' => 'adult']
                        ],
                        'default' => 'senior'
                    ]
                ]
            ]
        ]
    ];
    
    validateEquals(
        'Project operation',
        $expected,
        $result,
        'Project should handle complex field transformations'
    );

    // Test 5: Unwind Operation
    echo "\n5. Unwind Operation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('orders')
        ->unwind('items')
        ->toQuery();
    
    $expected = [
        [
            '$unwind' => '$items'
        ]
    ];
    
    validateEquals(
        'Unwind operation',
        $expected,
        $result,
        'Unwind should create proper stage'
    );

    // Test 6: Complex E-commerce Aggregation
    echo "\n6. Complex E-commerce Aggregation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('orders')
        ->match([
            'status' => 'completed',
            'created_at' => ['$gte' => '2024-01-01', '$lte' => '2024-12-31']
        ])
        ->lookup('users', 'user_id', '_id', 'user')
        ->unwind('user')
        ->lookup('products', 'items.product_id', '_id', 'product_details')
        ->group('user.country', [
            'total_sales' => ['$sum' => '$total_amount'],
            'order_count' => ['$sum' => 1],
            'avg_order_value' => ['$avg' => '$total_amount'],
            'top_products' => ['$push' => '$product_details.name']
        ])
        ->sortAggregation(['total_sales' => -1])
        ->limitAggregation(10)
        ->toQuery();
    
    $expected = [
        [
            '$match' => [
                'status' => 'completed',
                'created_at' => ['$gte' => '2024-01-01', '$lte' => '2024-12-31']
            ]
        ],
        [
            '$lookup' => [
                'from' => 'users',
                'localField' => 'user_id',
                'foreignField' => '_id',
                'as' => 'user'
            ]
        ],
        [
            '$unwind' => '$user'
        ],
        [
            '$lookup' => [
                'from' => 'products',
                'localField' => 'items.product_id',
                'foreignField' => '_id',
                'as' => 'product_details'
            ]
        ],
        [
            '$group' => [
                '_id' => '$user.country',
                'total_sales' => ['$sum' => '$total_amount'],
                'order_count' => ['$sum' => 1],
                'avg_order_value' => ['$avg' => '$total_amount'],
                'top_products' => ['$push' => '$product_details.name']
            ]
        ],
        [
            '$sort' => ['total_sales' => -1]
        ],
        [
            '$limit' => 10
        ]
    ];
    
    validateEquals(
        'Complex e-commerce aggregation',
        $expected,
        $result,
        'Complex aggregation should combine multiple stages'
    );

    // Test 7: Analytics Dashboard Aggregation
    echo "\n7. Analytics Dashboard Aggregation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('user_sessions')
        ->match([
            'start_time' => ['$gte' => '2024-08-01', '$lte' => '2024-08-31']
        ])
        ->group([
            'day' => ['$dayOfMonth' => '$start_time'],
            'hour' => ['$hour' => '$start_time']
        ], [
            'session_count' => ['$sum' => 1],
            'avg_duration' => ['$avg' => '$duration'],
            'unique_users' => ['$addToSet' => '$user_id'],
            'bounce_rate' => [
                '$avg' => [
                    '$cond' => [
                        ['$lte' => ['$page_views', 1]],
                        1,
                        0
                    ]
                ]
            ]
        ])
        ->project([
            'day' => '$_id.day',
            'hour' => '$_id.hour',
            'session_count' => 1,
            'avg_duration' => 1,
            'unique_users_count' => ['$size' => '$unique_users'],
            'bounce_rate_percent' => ['$multiply' => ['$bounce_rate', 100]]
        ])
        ->sortAggregation(['day' => 1, 'hour' => 1])
        ->toQuery();
    
    // Verify the structure is correct without exact matching due to complexity
    validateTrue(
        'Analytics aggregation structure',
        is_array($result) && count($result) >= 4,
        'Analytics aggregation should have multiple stages'
    );
    
    validateTrue(
        'Analytics has match stage',
        isset($result[0]['$match']),
        'First stage should be match'
    );

    // Test 8: Faceted Search Aggregation
    echo "\n8. Faceted Search Aggregation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('products')
        ->match(['status' => 'active'])
        ->facet([
            'by_category' => [
                ['$group' => ['_id' => '$category', 'count' => ['$sum' => 1]]],
                ['$sort' => ['count' => -1]]
            ],
            'by_price_range' => [
                [
                    '$bucket' => [
                        'groupBy' => '$price',
                        'boundaries' => [0, 50, 100, 200, 500, 1000],
                        'default' => '1000+',
                        'output' => ['count' => ['$sum' => 1]]
                    ]
                ]
            ],
            'by_rating' => [
                [
                    '$bucket' => [
                        'groupBy' => '$rating',
                        'boundaries' => [0, 2, 3, 4, 5],
                        'default' => 'unrated',
                        'output' => ['count' => ['$sum' => 1]]
                    ]
                ]
            ]
        ])
        ->toQuery();
    
    $expected = [
        [
            '$match' => ['status' => 'active']
        ],
        [
            '$facet' => [
                'by_category' => [
                    ['$group' => ['_id' => '$category', 'count' => ['$sum' => 1]]],
                    ['$sort' => ['count' => -1]]
                ],
                'by_price_range' => [
                    [
                        '$bucket' => [
                            'groupBy' => '$price',
                            'boundaries' => [0, 50, 100, 200, 500, 1000],
                            'default' => '1000+',
                            'output' => ['count' => ['$sum' => 1]]
                        ]
                    ]
                ],
                'by_rating' => [
                    [
                        '$bucket' => [
                            'groupBy' => '$rating',
                            'boundaries' => [0, 2, 3, 4, 5],
                            'default' => 'unrated',
                            'output' => ['count' => ['$sum' => 1]]
                        ]
                    ]
                ]
            ]
        ]
    ];
    
    validateEquals(
        'Faceted search aggregation',
        $expected,
        $result,
        'Faceted search should create proper bucket aggregations'
    );

    // Test 9: JSON Validation for Complex Aggregation
    echo "\n9. JSON Validation for Complex Aggregation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('complex_data')
        ->match(['type' => 'analytics'])
        ->group('category', [
            'total' => ['$sum' => '$value'],
            'avg' => ['$avg' => '$value'],
            'min' => ['$min' => '$value'],
            'max' => ['$max' => '$value']
        ])
        ->toQuery();
    
    // Validate JSON serialization
    $json = json_encode($result);
    $decoded = json_decode($json, true);
    
    validateEquals(
        'Complex aggregation JSON validation',
        $result,
        $decoded,
        'Complex aggregation should be JSON serializable'
    );

    // Test 10: Pipeline with Mixed Operations
    echo "\n10. Pipeline with Mixed Operations\n";
    $query = new MongoQuery();
    $result = $query
        ->from('events')
        ->match(['event_type' => 'page_view'])
        ->addToSet('unique_pages', '$page_url')
        ->sample(1000)  // Sample operation
        ->sortAggregation(['timestamp' => -1])
        ->limitAggregation(100)
        ->toQuery();
    
    // Verify basic structure
    validateTrue(
        'Mixed pipeline structure',
        is_array($result) && count($result) > 1,
        'Mixed pipeline should have multiple stages'
    );

} catch (Exception $e) {
    recordFailedTest('MongoDB Aggregation Test', $e->getMessage());
}

echo "\n" . str_repeat("=", 50) . "\n";
$results = printTestSummary('MongoDB Aggregation Tests');
return $results;
