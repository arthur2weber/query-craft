<?php
/**
 * MongoDB Advanced Filters Test - Simplified
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\MongoQuery;

echo "🧪 MONGODB ADVANCED FILTERS: Running Tests\n";
echo str_repeat("=", 50) . "\n\n";

resetTestCounters();

try {
    // Test 1: Regex Filtering
    echo "1. Regex Filtering\n";
    $query = new MongoQuery();
    $result = $query
        ->from('users')
        ->where('email', 'regex', '/.*@gmail\.com$/')
        ->toQuery();
    
    $expected = [
        'filter' => [
            'email' => ['$regex' => '/.*@gmail\.com$/']
        ],
        'options' => []
    ];
    
    validateEquals(
        'Regex filter',
        $expected,
        $result,
        'Regex operator should create proper MongoDB regex filter'
    );

    // Test 2: Size Operator
    echo "\n2. Array Size Operator\n";
    $query = new MongoQuery();
    $result = $query
        ->from('posts')
        ->where('tags', 'size', 3)
        ->toQuery();
    
    $expected = [
        'filter' => [
            'tags' => ['$size' => 3]
        ],
        'options' => []
    ];
    
    validateEquals(
        'Size operator',
        $expected,
        $result,
        'Size operator should filter arrays by length'
    );

    // Test 3: Exists Operator
    echo "\n3. Exists Operator\n";
    $query = new MongoQuery();
    $result = $query
        ->from('products')
        ->where('discount', 'exists', true)
        ->toQuery();
    
    $expected = [
        'filter' => [
            'discount' => ['$exists' => true]
        ],
        'options' => []
    ];
    
    validateEquals(
        'Exists operator',
        $expected,
        $result,
        'Exists operator should check field presence'
    );

    echo "\nBasic tests completed successfully!\n";

} catch (Exception $e) {
    recordFailedTest('MongoDB Advanced Filters Test', $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
$results = printTestSummary('MongoDB Advanced Filters Tests');
return $results;
