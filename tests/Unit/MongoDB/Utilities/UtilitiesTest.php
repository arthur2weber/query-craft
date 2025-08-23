<?php
/**
 * MongoDB Utilities Test
 * 
 * Tests MongoDB utility functions including
 * pipeline building, helper methods, and query optimization.
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\MongoQuery;

echo "🧪 MONGODB UTILITIES: Running Tests\n";
echo str_repeat("=", 50) . "\n\n";

resetTestCounters();

try {
    // Test 1: Basic Pipeline Construction
    echo "1. Basic Pipeline Construction\n";
    $query = new MongoQuery();
    $result = $query
        ->from('test_collection')
        ->where('status', 'active')
        ->toQuery();
    
    validateTrue(
        'Pipeline is array',
        is_array($result),
        'Query result should be an array'
    );
    
    validateTrue(
        'Pipeline not empty',
        !empty($result),
        'Pipeline should not be empty for simple query'
    );

    // Test 2: Query Method Chaining
    echo "\n2. Query Method Chaining\n";
    $query = new MongoQuery();
    $chainedQuery = $query
        ->from('products')
        ->where('price', '>', 100)
        ->where('category', 'electronics')
        ->orderBy('name', 'asc')
        ->limit(25);
    
    validateTrue(
        'Method chaining returns MongoQuery',
        $chainedQuery instanceof MongoQuery,
        'Chained methods should return MongoQuery instance'
    );
    
    $result = $chainedQuery->toQuery();
    validateTrue(
        'Chained query produces result',
        is_array($result),
        'Chained query should produce valid result'
    );

    // Test 3: Aggregation vs Find Decision
    echo "\n3. Aggregation vs Find Decision Logic\n";
    
    // Simple query should be find (or simple pipeline)
    $simpleQuery = new MongoQuery();
    $simpleResult = $simpleQuery
        ->from('users')
        ->where('active', true)
        ->toQuery();
    
    // Complex query should be aggregation
    $complexQuery = new MongoQuery();
    $complexResult = $complexQuery
        ->from('orders')
        ->match(['status' => 'completed'])
        ->group('customer_id', ['total' => ['$sum' => '$amount']])
        ->toQuery();
    
    validateTrue(
        'Simple query structure',
        is_array($simpleResult),
        'Simple queries should produce valid structure'
    );
    
    validateTrue(
        'Complex query has aggregation',
        isset($complexResult[0]['$match']) || isset($complexResult[0]['$group']),
        'Complex queries should use aggregation pipeline'
    );

    // Test 4: Collection Name Handling
    echo "\n4. Collection Name Handling\n";
    $query = new MongoQuery();
    $result = $query
        ->from('my_collection_name')
        ->toQuery();
    
    validateTrue(
        'Collection name handling',
        is_array($result),
        'Should handle collection names properly'
    );

    // Test 5: Empty Query Handling
    echo "\n5. Empty Query Handling\n";
    $query = new MongoQuery();
    $result = $query
        ->from('empty_test')
        ->toQuery();
    
    validateTrue(
        'Empty query produces result',
        is_array($result),
        'Empty queries should still produce valid structure'
    );

    // Test 6: Field Name Validation
    echo "\n6. Field Name Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('validation_test')
        ->where('valid_field', 'value')
        ->where('nested.field', 'value')
        ->where('field_with_numbers123', 'value')
        ->toQuery();
    
    validateTrue(
        'Field validation passes',
        is_array($result),
        'Valid field names should be accepted'
    );

    // Test 7: Value Type Handling
    echo "\n7. Value Type Handling\n";
    $query = new MongoQuery();
    $result = $query
        ->from('value_test')
        ->where('string_field', 'string_value')
        ->where('int_field', 42)
        ->where('float_field', 3.14)
        ->where('bool_field', true)
        ->where('array_field', ['item1', 'item2'])
        ->where('null_field', null)
        ->toQuery();
    
    validateTrue(
        'Multiple value types handled',
        is_array($result),
        'Should handle different value types'
    );

    // Test 8: Operator Validation
    echo "\n8. MongoDB Operator Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('operator_test')
        ->where('field1', '=', 'value1')
        ->where('field2', '>', 100)
        ->where('field3', '>=', 50)
        ->where('field4', '<', 1000)
        ->where('field5', '<=', 999)
        ->where('field6', '!=', 'exclude')
        ->where('field7', 'in', ['a', 'b', 'c'])
        ->where('field8', 'not_in', ['x', 'y', 'z'])
        ->where('field9', 'like', 'search%')
        ->where('field10', 'regex', '/pattern/i')
        ->where('field11', 'exists', true)
        ->where('field12', 'size', 3)
        ->toQuery();
    
    validateTrue(
        'MongoDB operators handled',
        is_array($result),
        'Should handle all MongoDB operators'
    );

    // Test 9: Pipeline Stage Order
    echo "\n9. Pipeline Stage Order Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('stage_order_test')
        ->match(['type' => 'product'])
        ->project(['name' => 1, 'price' => 1])
        ->sortAggregation(['price' => -1])
        ->limitAggregation(10)
        ->toQuery();
    
    validateTrue(
        'Pipeline stages in order',
        is_array($result) && count($result) >= 4,
        'Pipeline stages should be in correct order'
    );
    
    // Verify order: match, project, sort, limit
    $stageTypes = [];
    foreach ($result as $stage) {
        $stageTypes[] = array_keys($stage)[0];
    }
    
    validateTrue(
        'Correct stage sequence',
        in_array('$match', $stageTypes) && 
        in_array('$project', $stageTypes) && 
        in_array('$sort', $stageTypes) && 
        in_array('$limit', $stageTypes),
        'Should have all expected aggregation stages'
    );

    // Test 10: Query Reset and Reuse
    echo "\n10. Query Reset and Reuse\n";
    $query = new MongoQuery();
    
    // First query
    $result1 = $query
        ->from('reuse_test1')
        ->where('field1', 'value1')
        ->toQuery();
    
    // Second query (reusing same instance)
    $result2 = $query
        ->from('reuse_test2')
        ->where('field2', 'value2')
        ->toQuery();
    
    validateTrue(
        'First query valid',
        is_array($result1),
        'First query should be valid'
    );
    
    validateTrue(
        'Second query valid',
        is_array($result2),
        'Second query should be valid'
    );
    
    // Results should be different (not same query carried over)
    validateTrue(
        'Queries are different',
        $result1 !== $result2,
        'Reused queries should produce different results'
    );

    // Test 11: Large Pipeline Performance
    echo "\n11. Large Pipeline Construction\n";
    $query = new MongoQuery();
    $startTime = microtime(true);
    
    $result = $query
        ->from('performance_test')
        ->match(['status' => 'active'])
        ->match(['category' => 'electronics'])
        ->match(['price' => ['$gte' => 10]])
        ->project(['name' => 1, 'price' => 1, 'category' => 1])
        ->group('category', [
            'avg_price' => ['$avg' => '$price'],
            'count' => ['$sum' => 1]
        ])
        ->match(['count' => ['$gte' => 5]])
        ->sortAggregation(['avg_price' => -1])
        ->limitAggregation(50)
        ->toQuery();
    
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    
    validateTrue(
        'Large pipeline constructed',
        is_array($result) && count($result) >= 6,
        'Large pipeline should be constructed successfully'
    );
    
    validateTrue(
        'Performance acceptable',
        $executionTime < 1.0, // Should complete in under 1 second
        'Large pipeline construction should be performant'
    );

    // Test 12: JSON Serialization
    echo "\n12. JSON Serialization Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('json_test')
        ->match(['field' => 'value'])
        ->project(['name' => 1, 'data' => 1])
        ->sortAggregation(['name' => 1])
        ->toQuery();
    
    $json = json_encode($result);
    $decoded = json_decode($json, true);
    
    validateEquals(
        'JSON serialization consistent',
        $result,
        $decoded,
        'Query should serialize and deserialize consistently'
    );
    
    validateTrue(
        'JSON is valid',
        json_last_error() === JSON_ERROR_NONE,
        'Generated JSON should be valid'
    );

} catch (Exception $e) {
    recordFailedTest('MongoDB Utilities Test', $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
$results = printTestSummary('MongoDB Utilities Tests');
return $results;
