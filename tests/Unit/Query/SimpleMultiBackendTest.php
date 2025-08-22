<?php
/**
 * Simple Multi-Backend Test
 */

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../TestHelpers.php';

use Arthur2weber\QueryCraft\QueryCraft;

echo "🧪 MULTI-BACKEND: Simple Test\n";
echo str_repeat("=", 30) . "\n\n";

resetTestCounters();

try {
    // Test basic factory methods
    $elastic = QueryCraft::elastic();
    runTest("Can create ElasticQuery", !is_null($elastic));
    
    $mongo = QueryCraft::mongo();
    runTest("Can create MongoQuery", !is_null($mongo));
    
    $graph = QueryCraft::graph();
    runTest("Can create GraphQuery", !is_null($graph));
    
    // Test basic method chaining
    $query = QueryCraft::elastic()->where('status', 'active');
    runTest("Method chaining works", !is_null($query));
    
    // Test build method
    $result = $query->toQuery();
    runTest("Build method works", is_array($result));
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

printTestSummary("Simple Multi-Backend Tests");
