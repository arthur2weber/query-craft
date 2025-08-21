<?php
/**
 * Core Basic Query Tests
 * 
 * Tests fundamental query building functionality:
 * - Query initialization
 * - Basic structure
 * - Build method
 * - Query state management
 * 
 * @package Arthur2weber\QueryCraft\Tests\Unit\Core
 */

require_once __DIR__ . '/../../../src/ElasticQueryInterface.php';
require_once __DIR__ . '/../../../src/ElasticQuery.php';
require_once __DIR__ . '/../../TestHelpers.php';

use Arthur2weber\QueryCraft\ElasticQuery;

echo "🧪 CORE: Basic Query Tests\n";
echo str_repeat("=", 40) . "\n\n";

resetTestCounters();

// Test 1: Query initialization
$query = new ElasticQuery();
runTest("ElasticQuery instance creation", $query instanceof ElasticQuery);

// Test 2: Initial query structure
$emptyQuery = $query->build();
runTest("Initial query has bool structure", isset($emptyQuery['query']['bool']));

// Test 3: Query returns array
runTest("build() returns array", is_array($emptyQuery));

// Test 4: Query implements interface
runTest("Implements ElasticQueryInterface", $query instanceof \Arthur2weber\QueryCraft\ElasticQueryInterface);

// Test 5: Method chaining
$chainedQuery = (new ElasticQuery())->size(10)->from(0);
runTest("Method chaining works", $chainedQuery instanceof ElasticQuery);

// Test 6: Build produces valid structure
$basicQuery = (new ElasticQuery())
    ->size(10)
    ->from(0)
    ->build();
runTest("Basic query structure", 
    isset($basicQuery['size']) && 
    isset($basicQuery['from']) && 
    $basicQuery['size'] === 10 && 
    $basicQuery['from'] === 0
);

// Test 7: Empty bool query optimization
$emptyBoolQuery = (new ElasticQuery())->build();
runTest("Empty bool query structure", isset($emptyBoolQuery['query']['bool']));

// Test 8: Multiple builds produce same result
$query1 = (new ElasticQuery())->size(5);
$result1 = $query1->build();
$result2 = $query1->build();
runTest("Multiple builds are consistent", $result1 === $result2);

// Test 9: Immutability check (new instances are independent)
$baseQuery = new ElasticQuery();
$query1 = clone $baseQuery;
$query2 = clone $baseQuery;
$query1->size(10);
$query2->size(20);
runTest("Cloned instances are independent", 
    $query1->build()['size'] !== $query2->build()['size']
);

// Test 10: Complex query structure preservation
$complexQuery = (new ElasticQuery())
    ->size(15)
    ->from(30)
    ->build();
runTest("Complex query preserves all settings",
    $complexQuery['size'] === 15 && 
    $complexQuery['from'] === 30
);

return printTestSummary("Core Basic Tests");
