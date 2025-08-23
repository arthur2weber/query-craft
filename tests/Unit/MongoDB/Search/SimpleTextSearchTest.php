<?php
/**
 * MongoDB Text Search Test - Simplified
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\MongoQuery;

echo "🧪 MONGODB TEXT SEARCH: Running Tests\n";
echo str_repeat("=", 50) . "\n\n";

resetTestCounters();

try {
    // Test 1: Basic Text Search
    echo "1. Basic Text Search\n";
    $query = new MongoQuery();
    $result = $query
        ->from('articles')
        ->textSearch('mongodb database tutorial')
        ->toQuery();
    
    validateTrue(
        'Text search creates aggregation',
        is_array($result) && count($result) > 0,
        'Text search should create aggregation pipeline'
    );
    
    validateTrue(
        'Text search has match stage',
        isset($result[0]['$match']['$text']),
        'Should have text search match stage'
    );

    // Test 2: Text Search with Options
    echo "\n2. Text Search with Language\n";
    $query = new MongoQuery();
    $result = $query
        ->from('documents')
        ->textSearch('database performance', ['$language' => 'english'])
        ->toQuery();
    
    validateTrue(
        'Text search with language',
        isset($result[0]['$match']['$text']['$language']),
        'Should support language options'
    );

    // Test 3: Text Search Combined with Filters
    echo "\n3. Text Search Combined with Filters\n";
    $query = new MongoQuery();
    $result = $query
        ->from('blog_posts')
        ->textSearch('javascript tutorial')
        ->match(['status' => 'published'])
        ->toQuery();
    
    validateTrue(
        'Text search with filters',
        count($result) >= 2,
        'Should have multiple aggregation stages'
    );

    echo "\nAll tests completed successfully!\n";

} catch (Exception $e) {
    recordFailedTest('MongoDB Text Search Test', $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
$results = printTestSummary('MongoDB Text Search Tests');
return $results;
