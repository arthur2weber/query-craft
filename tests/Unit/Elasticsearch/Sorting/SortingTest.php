<?php
/**
 * Sorting Tests
 * 
 * Tests sorting functionality:
 * - sort() method
 * - addSort() method  
 * - orderBy() / orderByDesc() Eloquent methods
 * - latest() / oldest() methods
 * - Complex sorting combinations
 * - Geo-distance sorting
 * 
 * @package Arthur2weber\QueryCraft\Tests\Unit\Sorting
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\ElasticQuery;

echo "🧪 ELASTICSEARCH: Sorting Tests\n";
echo str_repeat("=", 40) . "\n\n";

$successCount = 0;
$totalTests = 0;

function runTest($description, $result) {
    global $successCount, $totalTests;
    $totalTests++;
    echo ($result ? "✅" : "❌") . " $description\n";
    if ($result) $successCount++;
}

// Test 1: Basic sort ascending
$sortAscQuery = (new ElasticQuery())
    ->sort('title', 'asc')
    ->build();
runTest("Basic sort ascending",
    isset($sortAscQuery['sort'][0]['title']['order']) &&
    $sortAscQuery['sort'][0]['title']['order'] === 'asc');

// Test 2: Basic sort descending
$sortDescQuery = (new ElasticQuery())
    ->sort('created_at', 'desc')
    ->build();
runTest("Basic sort descending",
    isset($sortDescQuery['sort'][0]['created_at']['order']) &&
    $sortDescQuery['sort'][0]['created_at']['order'] === 'desc');

// Test 3: orderBy() Eloquent method
$orderByQuery = (new ElasticQuery())
    ->orderBy('price', 'asc')
    ->build();
runTest("orderBy() Eloquent method",
    isset($orderByQuery['sort'][0]['price']['order']) &&
    $orderByQuery['sort'][0]['price']['order'] === 'asc');

// Test 4: orderByDesc() Eloquent method
$orderByDescQuery = (new ElasticQuery())
    ->orderByDesc('rating')
    ->build();
runTest("orderByDesc() Eloquent method",
    isset($orderByDescQuery['sort'][0]['rating']['order']) &&
    $orderByDescQuery['sort'][0]['rating']['order'] === 'desc');

// Test 5: latest() method
$latestQuery = (new ElasticQuery())
    ->latest('created_at')
    ->build();
runTest("latest() method",
    isset($latestQuery['sort'][0]['created_at']['order']) &&
    $latestQuery['sort'][0]['created_at']['order'] === 'desc');

// Test 6: latest() with default field
$latestDefaultQuery = (new ElasticQuery())
    ->latest()
    ->build();
runTest("latest() with default field",
    isset($latestDefaultQuery['sort'][0]['created_at']['order']) &&
    $latestDefaultQuery['sort'][0]['created_at']['order'] === 'desc');

// Test 7: oldest() method
$oldestQuery = (new ElasticQuery())
    ->oldest('updated_at')
    ->build();
runTest("oldest() method",
    isset($oldestQuery['sort'][0]['updated_at']['order']) &&
    $oldestQuery['sort'][0]['updated_at']['order'] === 'asc');

// Test 8: Multiple sort fields
$multiSortQuery = (new ElasticQuery())
    ->sort('_score', 'desc')
    ->sort('created_at', 'desc')
    ->sort('title', 'asc')
    ->build();
runTest("Multiple sort fields",
    isset($multiSortQuery['sort']) &&
    count($multiSortQuery['sort']) === 3 &&
    $multiSortQuery['sort'][0]['_score']['order'] === 'desc' &&
    $multiSortQuery['sort'][1]['created_at']['order'] === 'desc' &&
    $multiSortQuery['sort'][2]['title']['order'] === 'asc');

// Test 9: Advanced sort with array configuration
$advancedSortQuery = (new ElasticQuery())
    ->sort('price', ['order' => 'asc', 'missing' => '_last'])
    ->build();
runTest("Advanced sort with array configuration",
    isset($advancedSortQuery['sort'][0]['price']['order']) &&
    isset($advancedSortQuery['sort'][0]['price']['missing']) &&
    $advancedSortQuery['sort'][0]['price']['order'] === 'asc' &&
    $advancedSortQuery['sort'][0]['price']['missing'] === '_last');

// Test 10: addSort() with custom configuration
$addSortQuery = (new ElasticQuery())
    ->addSort([
        '_geo_distance' => [
            'location' => '40.7128,-74.0060',
            'order' => 'asc',
            'unit' => 'km'
        ]
    ])
    ->build();
runTest("addSort() with geo-distance",
    isset($addSortQuery['sort'][0]['_geo_distance']['location']) &&
    isset($addSortQuery['sort'][0]['_geo_distance']['order']) &&
    $addSortQuery['sort'][0]['_geo_distance']['order'] === 'asc');

// Test 11: Script-based sorting
$scriptSortQuery = (new ElasticQuery())
    ->addSort([
        '_script' => [
            'type' => 'number',
            'script' => ['source' => 'doc["likes"].value + doc["shares"].value'],
            'order' => 'desc'
        ]
    ])
    ->build();
runTest("Script-based sorting",
    isset($scriptSortQuery['sort'][0]['_script']['script']['source']) &&
    $scriptSortQuery['sort'][0]['_script']['order'] === 'desc');

// Test 12: Sorting with search query
$sortWithSearchQuery = (new ElasticQuery())
    ->search('Laravel tutorial', ['title^2', 'content'])
    ->orderByDesc('_score')
    ->orderByDesc('created_at')
    ->orderBy('title', 'asc')
    ->build();
runTest("Sorting with search query",
    isset($sortWithSearchQuery['query']['bool']['must']) &&
    isset($sortWithSearchQuery['sort']) &&
    count($sortWithSearchQuery['sort']) === 3);

// Test 13: Mixed Eloquent sorting methods
$mixedSortQuery = (new ElasticQuery())
    ->latest('published_at')
    ->orderByDesc('views')
    ->orderBy('title', 'asc')
    ->build();
runTest("Mixed Eloquent sorting methods",
    isset($mixedSortQuery['sort']) &&
    count($mixedSortQuery['sort']) === 3);

// Test 14: Invalid sort direction validation
try {
    (new ElasticQuery())->sort('field', 'invalid');
    runTest("Invalid sort direction validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Invalid sort direction validation", true);
}

// Test 15: Sort with filters and aggregations
$complexSortQuery = (new ElasticQuery())
    ->where('status', 'published')
    ->range('rating', 'gte', 4.0)
    ->orderByDesc('_score')
    ->orderByDesc('created_at')
    ->aggregation('avg_rating', ['avg' => ['field' => 'rating']])
    ->build();
runTest("Sort with filters and aggregations",
    isset($complexSortQuery['query']['bool']['filter']) &&
    isset($complexSortQuery['sort']) &&
    isset($complexSortQuery['aggs']) &&
    count($complexSortQuery['sort']) === 2);

// Test 16: Sorting priority order
$prioritySortQuery = (new ElasticQuery())
    ->search('test query', ['title'])
    ->sort('_score', 'desc')      // First priority: relevance
    ->sort('featured', 'desc')    // Second priority: featured
    ->sort('created_at', 'desc')  // Third priority: recency
    ->build();
runTest("Sorting priority order",
    isset($prioritySortQuery['sort']) &&
    count($prioritySortQuery['sort']) === 3 &&
    isset($prioritySortQuery['sort'][0]['_score']) &&
    isset($prioritySortQuery['sort'][1]['featured']) &&
    isset($prioritySortQuery['sort'][2]['created_at']));

echo "\n" . str_repeat("=", 40) . "\n";
echo "📊 Sorting Tests: $successCount/$totalTests passed\n";
echo "📈 Success Rate: " . round(($successCount/$totalTests) * 100, 1) . "%\n";

return ['passed' => $successCount, 'total' => $totalTests];
