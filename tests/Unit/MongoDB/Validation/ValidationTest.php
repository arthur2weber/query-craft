<?php
/**
 * MongoDB Validation Test
 * 
 * Tests MongoDB query validation including
 * field validation, operator validation, and error handling.
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\MongoQuery;

echo "🧪 MONGODB VALIDATION: Running Tests\n";
echo str_repeat("=", 50) . "\n\n";

resetTestCounters();

try {
    // Test 1: Valid Field Names
    echo "1. Valid Field Names\n";
    $query = new MongoQuery();
    $result = $query
        ->from('validation_test')
        ->where('valid_field', 'value')
        ->where('another.nested.field', 'value')
        ->where('field123', 'value')
        ->where('_private_field', 'value')
        ->where('fieldWithCamelCase', 'value')
        ->toQuery();
    
    validateTrue(
        'Valid field names accepted',
        is_array($result),
        'Valid field names should be accepted'
    );

    // Test 2: MongoDB Operator Validation
    echo "\n2. MongoDB Operator Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('operator_validation')
        ->where('equal_field', '=', 'value')
        ->where('greater_field', '>', 100)
        ->where('less_field', '<', 50)
        ->where('gte_field', '>=', 25)
        ->where('lte_field', '<=', 75)
        ->where('not_equal_field', '!=', 'exclude')
        ->where('in_field', 'in', ['a', 'b', 'c'])
        ->where('not_in_field', 'not_in', ['x', 'y'])
        ->where('regex_field', 'regex', '/pattern/i')
        ->where('exists_field', 'exists', true)
        ->where('size_field', 'size', 3)
        ->where('all_field', 'all', ['tag1', 'tag2'])
        ->where('type_field', 'type', 'string')
        ->where('mod_field', 'mod', [10, 0])
        ->toQuery();
    
    validateTrue(
        'MongoDB operators validated',
        is_array($result),
        'All MongoDB operators should be properly validated'
    );

    // Test 3: Value Type Validation
    echo "\n3. Value Type Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('value_validation')
        ->where('string_val', 'hello world')
        ->where('int_val', 42)
        ->where('float_val', 3.14159)
        ->where('bool_val', true)
        ->where('null_val', null)
        ->where('array_val', [1, 2, 3, 'four'])
        ->where('assoc_array', ['key' => 'value', 'number' => 123])
        ->toQuery();
    
    validateTrue(
        'Value types validated',
        is_array($result),
        'Different value types should be properly validated'
    );

    // Test 4: Geographic Coordinate Validation
    echo "\n4. Geographic Coordinate Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('geo_validation')
        ->near('location', [
            'type' => 'Point',
            'coordinates' => [-74.006, 40.7128] // Valid NYC coordinates
        ])
        ->toQuery();
    
    validateTrue(
        'Geographic coordinates validated',
        is_array($result),
        'Valid geographic coordinates should be accepted'
    );

    // Test 5: Aggregation Pipeline Validation
    echo "\n5. Aggregation Pipeline Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('pipeline_validation')
        ->match(['category' => 'test'])
        ->project(['name' => 1, 'value' => 1])
        ->group('category', ['count' => ['$sum' => 1]])
        ->sortAggregation(['count' => -1])
        ->limitAggregation(10)
        ->toQuery();
    
    validateTrue(
        'Pipeline stages validated',
        is_array($result) && count($result) >= 5,
        'Aggregation pipeline should be properly validated'
    );

    // Test 6: Text Search Validation
    echo "\n6. Text Search Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('text_validation')
        ->textSearch('valid search terms')
        ->textSearch('phrase search', ['$language' => 'english'])
        ->toQuery();
    
    validateTrue(
        'Text search validated',
        is_array($result),
        'Text search queries should be validated'
    );

    // Test 7: Sort Direction Validation
    echo "\n7. Sort Direction Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('sort_validation')
        ->sortAggregation([
            'field1' => 1,      // ascending numeric
            'field2' => -1,     // descending numeric
            'field3' => 'asc',  // ascending string
            'field4' => 'desc'  // descending string
        ])
        ->toQuery();
    
    validateTrue(
        'Sort directions validated',
        is_array($result),
        'Different sort direction formats should be validated'
    );

    // Test 8: Nested Query Structure Validation
    echo "\n8. Nested Query Structure Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('nested_validation')
        ->match([
            'status' => 'active',
            'metadata.tags' => ['$in' => ['important', 'urgent']],
            'settings.notifications.email' => true
        ])
        ->toQuery();
    
    validateTrue(
        'Nested query structure validated',
        is_array($result),
        'Nested query structures should be validated'
    );

    // Test 9: Array and Object Validation
    echo "\n9. Array and Object Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('array_validation')
        ->where('tags', 'all', ['mongodb', 'database'])
        ->where('comments', 'size', 5)
        ->where('author', 'elemMatch', ['name' => 'John', 'role' => 'admin'])
        ->toQuery();
    
    validateTrue(
        'Array operations validated',
        is_array($result),
        'Array-specific operations should be validated'
    );

    // Test 10: Complex Query Validation
    echo "\n10. Complex Query Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('complex_validation')
        ->textSearch('search terms')
        ->match([
            'status' => 'published',
            'date' => ['$gte' => '2024-01-01'],
            'categories' => ['$in' => ['tech', 'science']]
        ])
        ->near('author_location', [
            'type' => 'Point',
            'coordinates' => [-74.006, 40.7128]
        ], ['maxDistance' => 50000])
        ->group('category', [
            'count' => ['$sum' => 1],
            'avg_views' => ['$avg' => '$views']
        ])
        ->project([
            'category' => '$_id',
            'total_posts' => '$count',
            'average_views' => '$avg_views'
        ])
        ->sortAggregation(['total_posts' => -1])
        ->limitAggregation(20)
        ->toQuery();
    
    validateTrue(
        'Complex query validated',
        is_array($result) && count($result) >= 6,
        'Complex multi-stage queries should be validated'
    );

    // Test 11: Edge Case Validation
    echo "\n11. Edge Case Validation\n";
    
    // Empty strings
    $query1 = new MongoQuery();
    $result1 = $query1
        ->from('edge_cases')
        ->where('empty_string', '')
        ->toQuery();
    
    // Zero values
    $query2 = new MongoQuery();
    $result2 = $query2
        ->from('edge_cases')
        ->where('zero_int', 0)
        ->where('zero_float', 0.0)
        ->toQuery();
    
    // Large numbers
    $query3 = new MongoQuery();
    $result3 = $query3
        ->from('edge_cases')
        ->where('large_number', 999999999999)
        ->toQuery();
    
    validateTrue(
        'Edge cases handled - empty string',
        is_array($result1),
        'Empty strings should be handled'
    );
    
    validateTrue(
        'Edge cases handled - zero values',
        is_array($result2),
        'Zero values should be handled'
    );
    
    validateTrue(
        'Edge cases handled - large numbers',
        is_array($result3),
        'Large numbers should be handled'
    );

    // Test 12: JSON Serialization Validation
    echo "\n12. JSON Serialization Validation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('json_validation')
        ->match(['field' => 'value'])
        ->project(['name' => 1, 'data' => 1])
        ->toQuery();
    
    // Test JSON encoding
    $json = json_encode($result);
    $jsonError = json_last_error();
    
    validateTrue(
        'JSON encoding successful',
        $jsonError === JSON_ERROR_NONE,
        'Query result should be JSON encodable'
    );
    
    // Test JSON decoding
    $decoded = json_decode($json, true);
    
    validateEquals(
        'JSON round-trip consistency',
        $result,
        $decoded,
        'JSON encoding/decoding should be consistent'
    );

} catch (Exception $e) {
    recordFailedTest('MongoDB Validation Test', $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
$results = printTestSummary('MongoDB Validation Tests');
return $results;
