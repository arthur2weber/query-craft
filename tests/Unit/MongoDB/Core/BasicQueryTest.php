<?php
/**
 * MongoDB Basic Query Test
 * 
 * Tests basic MongoDB query building functionality including
 * where clauses, find operations, and basic query structure validation.
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\MongoQuery;

echo "🧪 MONGODB BASIC QUERY: Running Tests\n";
echo str_repeat("=", 50) . "\n\n";

resetTestCounters();

try {
    // Test 1: Basic Query Creation
    echo "1. Basic Query Creation\n";
    $query = new MongoQuery();
    $result = $query->from('users')->toQuery();
    
    validateEquals(
        'Basic query collection',
        ['filter' => [], 'options' => []],
        $result,
        'Empty query should return empty filter and options'
    );

    // Test 2: Simple Where Clause
    echo "\n2. Simple Where Clause\n";
    $query = new MongoQuery();
    $result = $query
        ->from('users')
        ->where('status', 'active')
        ->toQuery();
    
    $expected = [
        'filter' => ['status' => 'active'],
        'options' => []
    ];
    
    validateEquals(
        'Simple where clause',
        $expected,
        $result,
        'Where clause should create proper MongoDB filter'
    );

    // Test 3: Multiple Where Clauses
    echo "\n3. Multiple Where Clauses\n";
    $query = new MongoQuery();
    $result = $query
        ->from('users')
        ->where('status', 'active')
        ->where('age', '>=', 18)
        ->where('country', 'US')
        ->toQuery();
    
    $expected = [
        'filter' => [
            'status' => 'active',
            'age' => ['$gte' => 18],
            'country' => 'US'
        ],
        'options' => []
    ];
    
    validateEquals(
        'Multiple where clauses',
        $expected,
        $result,
        'Multiple where clauses should combine properly'
    );

    // Test 4: Where In Clause
    echo "\n4. Where In Clause\n";
    $query = new MongoQuery();
    $result = $query
        ->from('products')
        ->whereIn('category', ['electronics', 'books', 'clothing'])
        ->toQuery();
    
    $expected = [
        'filter' => [
            'category' => ['$in' => ['electronics', 'books', 'clothing']]
        ],
        'options' => []
    ];
    
    validateEquals(
        'Where in clause',
        $expected,
        $result,
        'WhereIn should create proper MongoDB $in operator'
    );

    // Test 5: Where Not In Clause
    echo "\n5. Where Not In Clause\n";
    $query = new MongoQuery();
    $result = $query
        ->from('products')
        ->whereNotIn('status', ['archived', 'deleted'])
        ->toQuery();
    
    $expected = [
        'filter' => [
            'status' => ['$nin' => ['archived', 'deleted']]
        ],
        'options' => []
    ];
    
    validateEquals(
        'Where not in clause',
        $expected,
        $result,
        'WhereNotIn should create proper MongoDB $nin operator'
    );

    // Test 6: Where Null and Not Null
    echo "\n6. Where Null and Not Null\n";
    $query = new MongoQuery();
    $result = $query
        ->from('users')
        ->whereNull('deleted_at')
        ->whereNotNull('email')
        ->toQuery();
    
    $expected = [
        'filter' => [
            'deleted_at' => null,
            'email' => ['$ne' => null]
        ],
        'options' => []
    ];
    
    validateEquals(
        'Where null/not null',
        $expected,
        $result,
        'Null checks should work properly'
    );

    // Test 7: Where Between
    echo "\n7. Where Between\n";
    $query = new MongoQuery();
    $result = $query
        ->from('orders')
        ->whereBetween('total', [100, 500])
        ->toQuery();
    
    $expected = [
        'filter' => [
            'total' => ['$gte' => 100, '$lte' => 500]
        ],
        'options' => []
    ];
    
    validateEquals(
        'Where between',
        $expected,
        $result,
        'WhereBetween should create proper range query'
    );

    // Test 8: Complex Operators
    echo "\n8. Complex Operators\n";
    $query = new MongoQuery();
    $result = $query
        ->from('products')
        ->where('price', '>', 100)
        ->where('rating', '<=', 4.5)
        ->where('stock', '!=', 0)
        ->toQuery();
    
    $expected = [
        'filter' => [
            'price' => ['$gt' => 100],
            'rating' => ['$lte' => 4.5],
            'stock' => ['$ne' => 0]
        ],
        'options' => []
    ];
    
    validateEquals(
        'Complex operators',
        $expected,
        $result,
        'Complex operators should map to MongoDB operators'
    );

    // Test 9: Sorting
    echo "\n9. Sorting\n";
    $query = new MongoQuery();
    $result = $query
        ->from('users')
        ->where('status', 'active')
        ->orderBy('name', 'asc')
        ->orderBy('created_at', 'desc')
        ->toQuery();
    
    $expected = [
        'filter' => ['status' => 'active'],
        'options' => [
            'sort' => [
                'name' => 1,
                'created_at' => -1
            ]
        ]
    ];
    
    validateEquals(
        'Sorting',
        $expected,
        $result,
        'Sorting should convert to MongoDB sort format'
    );

    // Test 10: Pagination
    echo "\n10. Pagination\n";
    $query = new MongoQuery();
    $result = $query
        ->from('products')
        ->where('status', 'active')
        ->take(20)
        ->skip(40)
        ->toQuery();
    
    $expected = [
        'filter' => ['status' => 'active'],
        'options' => [
            'limit' => 20,
            'skip' => 40
        ]
    ];
    
    validateEquals(
        'Pagination',
        $expected,
        $result,
        'Pagination should add limit and skip options'
    );

    // Test 11: Select Fields (Projection)
    echo "\n11. Select Fields (Projection)\n";
    $query = new MongoQuery();
    $result = $query
        ->from('users')
        ->select(['name', 'email', 'status'])
        ->where('active', true)
        ->toQuery();
    
    $expected = [
        'filter' => ['active' => true],
        'options' => [
            'projection' => [
                'name' => 1,
                'email' => 1,
                'status' => 1
            ]
        ]
    ];
    
    validateEquals(
        'Select fields projection',
        $expected,
        $result,
        'Select should create proper MongoDB projection'
    );

    // Test 12: Combined Complex Query (simplified validation)
    echo "\n12. Combined Complex Query\n";
    $query = new MongoQuery();
    $result = $query
        ->from('orders')
        ->select(['order_id', 'customer_name', 'total', 'status'])
        ->where('status', 'completed')
        ->whereBetween('total', [50, 1000])
        ->whereIn('payment_method', ['credit_card', 'paypal'])
        ->whereNotNull('customer_email')
        ->orderBy('created_at', 'desc')
        ->take(25)
        ->skip(50)
        ->toQuery();
    
    // Validate structure without exact matching
    validateTrue(
        'Combined query has filter',
        isset($result['filter']) && is_array($result['filter']),
        'Should have filter section'
    );
    
    validateTrue(
        'Combined query has options',
        isset($result['options']) && is_array($result['options']),
        'Should have options section'
    );
    
    validateTrue(
        'Combined query has projection',
        isset($result['options']['projection']),
        'Should have projection in options'
    );
    
    validateTrue(
        'Combined query has pagination',
        isset($result['options']['limit']) && isset($result['options']['skip']),
        'Should have pagination options'
    );

    // Test 13: JSON Validation
    echo "\n13. JSON Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('complex_data')
        ->where('metadata.version', '>=', '2.0')
        ->whereIn('tags', ['important', 'urgent'])
        ->toQuery();
    
    // Validate that the result can be properly JSON encoded
    $json = json_encode($result);
    $decoded = json_decode($json, true);
    
    validateEquals(
        'JSON encoding validation',
        $result,
        $decoded,
        'Query should be JSON serializable'
    );

} catch (Exception $e) {
    recordFailedTest('MongoDB Basic Query Test', $e->getMessage());
}

echo "\n" . str_repeat("=", 50) . "\n";
$results = printTestSummary('MongoDB Basic Query Tests');
return $results;
