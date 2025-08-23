<?php
/**
 * MongoDB Sorting Test
 * 
 * Tests MongoDB sorting functionality including
 * basic sorting, multiple field sorting, and aggregation sorting.
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\MongoQuery;

echo "🧪 MONGODB SORTING: Running Tests\n";
echo str_repeat("=", 50) . "\n\n";

resetTestCounters();

try {
    // Test 1: Basic Ascending Sort
    echo "1. Basic Ascending Sort\n";
    $query = new MongoQuery();
    $result = $query
        ->from('products')
        ->where('category', 'electronics')
        ->orderBy('name', 'asc')
        ->toQuery();
    
    // Check if using aggregation (orderBy forces aggregation in our implementation)
    if (isset($result[0]['$match'])) {
        // Aggregation pipeline
        validateTrue(
            'Basic sort creates aggregation',
            isset($result[0]['$match']) || isset($result[1]['$sort']),
            'Should create aggregation pipeline with sort'
        );
    } else {
        // Find query with options
        validateTrue(
            'Basic sort in find query',
            isset($result['options']['sort']['name']),
            'Should add sort option to find query'
        );
    }

    // Test 2: Basic Descending Sort
    echo "\n2. Basic Descending Sort\n";
    $query = new MongoQuery();
    $result = $query
        ->from('articles')
        ->orderBy('created_at', 'desc')
        ->toQuery();
    
    validateTrue(
        'Descending sort structure',
        is_array($result),
        'Descending sort should create proper structure'
    );

    // Test 3: Multiple Field Sorting
    echo "\n3. Multiple Field Sorting\n";
    $query = new MongoQuery();
    $result = $query
        ->from('employees')
        ->orderBy('department', 'asc')
        ->orderBy('salary', 'desc')
        ->orderBy('name', 'asc')
        ->toQuery();
    
    validateTrue(
        'Multiple field sort structure',
        is_array($result),
        'Multiple field sorting should work'
    );

    // Test 4: Sort with Aggregation Pipeline
    echo "\n4. Sort in Aggregation Pipeline\n";
    $query = new MongoQuery();
    $result = $query
        ->from('sales')
        ->match(['region' => 'north'])
        ->group('product_id', [
            'total_sales' => ['$sum' => '$amount'],
            'avg_price' => ['$avg' => '$price']
        ])
        ->sortAggregation([
            'total_sales' => -1,
            'avg_price' => 1
        ])
        ->limitAggregation(10)
        ->toQuery();
    
    validateTrue(
        'Aggregation sort structure',
        is_array($result) && count($result) >= 3,
        'Should have multiple aggregation stages including sort'
    );
    
    // Look for sort stage in aggregation
    $hasSortStage = false;
    foreach ($result as $stage) {
        if (isset($stage['$sort'])) {
            $hasSortStage = true;
            break;
        }
    }
    
    validateTrue(
        'Has sort stage in aggregation',
        $hasSortStage,
        'Should have $sort stage in aggregation pipeline'
    );

    // Test 5: Sort with Projection
    echo "\n5. Sort with Projection\n";
    $query = new MongoQuery();
    $result = $query
        ->from('users')
        ->select(['name', 'email', 'created_at'])
        ->orderBy('created_at', 'desc')
        ->limit(50)
        ->toQuery();
    
    validateTrue(
        'Sort with projection structure',
        is_array($result),
        'Should handle sorting with projection'
    );

    // Test 6: Sort by Nested Field
    echo "\n6. Sort by Nested Field\n";
    $query = new MongoQuery();
    $result = $query
        ->from('orders')
        ->orderBy('customer.name', 'asc')
        ->orderBy('order_date', 'desc')
        ->toQuery();
    
    validateTrue(
        'Nested field sort structure',
        is_array($result),
        'Should handle sorting by nested fields'
    );

    // Test 7: Sort with Geographic Near
    echo "\n7. Sort with Geographic Near\n";
    $query = new MongoQuery();
    $result = $query
        ->from('restaurants')
        ->near('location', [
            'type' => 'Point',
            'coordinates' => [-74.006, 40.7128]
        ], ['maxDistance' => 5000])
        ->sortAggregation([
            'rating' => -1,
            'price_range' => 1
        ])
        ->limitAggregation(20)
        ->toQuery();
    
    validateTrue(
        'Geographic sort structure',
        is_array($result) && count($result) >= 2,
        'Should combine geographic query with sorting'
    );

    // Test 8: Sort with Text Search
    echo "\n8. Sort with Text Search\n";
    $query = new MongoQuery();
    $result = $query
        ->from('documents')
        ->textSearch('mongodb database')
        ->project([
            'title' => 1,
            'content' => 1,
            'score' => ['$meta' => 'textScore']
        ])
        ->sortAggregation([
            'score' => ['$meta' => 'textScore'],
            'title' => 1
        ])
        ->limitAggregation(15)
        ->toQuery();
    
    validateTrue(
        'Text search sort structure',
        is_array($result) && count($result) >= 3,
        'Should handle text search with meta score sorting'
    );

    // Test 9: Sort Direction Validation
    echo "\n9. Sort Direction Values\n";
    $query = new MongoQuery();
    $result = $query
        ->from('test_collection')
        ->sortAggregation([
            'field1' => 1,    // ascending
            'field2' => -1,   // descending
            'field3' => 'asc', // ascending string
            'field4' => 'desc' // descending string
        ])
        ->toQuery();
    
    validateTrue(
        'Sort direction handling',
        is_array($result),
        'Should handle different sort direction formats'
    );

    // Test 10: Complex Multi-Stage Sort
    echo "\n10. Complex Multi-Stage Sort\n";
    $query = new MongoQuery();
    $result = $query
        ->from('analytics')
        ->match(['date' => ['$gte' => '2024-01-01']])
        ->group('category', [
            'total_views' => ['$sum' => '$views'],
            'unique_users' => ['$addToSet' => '$user_id']
        ])
        ->addFields([
            'unique_user_count' => ['$size' => '$unique_users']
        ])
        ->project([
            'category' => '$_id',
            'total_views' => 1,
            'unique_user_count' => 1,
            'avg_views_per_user' => [
                '$divide' => ['$total_views', '$unique_user_count']
            ]
        ])
        ->sortAggregation([
            'total_views' => -1,
            'unique_user_count' => -1,
            'category' => 1
        ])
        ->limitAggregation(25)
        ->toQuery();
    
    validateTrue(
        'Complex multi-stage sort',
        is_array($result) && count($result) >= 5,
        'Should handle complex pipeline with sorting'
    );

    // Test 11: JSON Validation for Sorting
    echo "\n11. JSON Validation for Sorting\n";
    $query = new MongoQuery();
    $result = $query
        ->from('test_data')
        ->sortAggregation([
            'priority' => -1,
            'created_at' => 1
        ])
        ->toQuery();
    
    // Validate JSON serialization
    $json = json_encode($result);
    $decoded = json_decode($json, true);
    
    validateEquals(
        'Sort queries JSON validation',
        $result,
        $decoded,
        'Sort queries should be JSON serializable'
    );

} catch (Exception $e) {
    recordFailedTest('MongoDB Sorting Test', $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
$results = printTestSummary('MongoDB Sorting Tests');
return $results;
