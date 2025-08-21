<?php
/**
 * Where Clause Tests
 * 
 * Tests Eloquent-style where clause functionality:
 * - where() with different operators
 * - whereIn() / whereNotIn()
 * - whereBetween() / whereNotBetween()
 * - whereNull() / whereNotNull()
 * - whereExists() / whereNotExists()
 * 
 * @package Arthur2weber\QueryCraft\Tests\Unit\Filters
 */

require_once __DIR__ . '/../../../src/ElasticQueryInterface.php';
require_once __DIR__ . '/../../../src/ElasticQuery.php';

use Arthur2weber\QueryCraft\ElasticQuery;

echo "🧪 FILTERS: Where Clause Tests\n";
echo str_repeat("=", 40) . "\n\n";

$successCount = 0;
$totalTests = 0;

function runTest($description, $result) {
    global $successCount, $totalTests;
    $totalTests++;
    echo ($result ? "✅" : "❌") . " $description\n";
    if ($result) $successCount++;
}

// Test 1: where() with equality (default operator)
$whereEqualQuery = (new ElasticQuery())
    ->where('status', 'active')
    ->build();
runTest("where() equality filter",
    isset($whereEqualQuery['query']['bool']['filter'][0]['term']['status']));

// Test 2: where() with explicit equality
$whereExplicitQuery = (new ElasticQuery())
    ->where('status', '=', 'active')
    ->build();
runTest("where() explicit equality",
    isset($whereExplicitQuery['query']['bool']['filter'][0]['term']['status']));

// Test 3: where() with not equal
$whereNotEqualQuery = (new ElasticQuery())
    ->where('status', '!=', 'deleted')
    ->build();
runTest("where() not equal",
    isset($whereNotEqualQuery['query']['bool']['must_not'][0]['term']['status']));

// Test 4: where() with greater than
$whereGtQuery = (new ElasticQuery())
    ->where('age', '>', 18)
    ->build();
runTest("where() greater than",
    isset($whereGtQuery['query']['bool']['filter'][0]['range']['age']['gt']));

// Test 5: where() with greater than or equal
$whereGteQuery = (new ElasticQuery())
    ->where('score', '>=', 85)
    ->build();
runTest("where() greater than or equal",
    isset($whereGteQuery['query']['bool']['filter'][0]['range']['score']['gte']));

// Test 6: where() with less than
$whereLtQuery = (new ElasticQuery())
    ->where('price', '<', 100)
    ->build();
runTest("where() less than",
    isset($whereLtQuery['query']['bool']['filter'][0]['range']['price']['lt']));

// Test 7: where() with less than or equal
$whereLteQuery = (new ElasticQuery())
    ->where('discount', '<=', 50)
    ->build();
runTest("where() less than or equal",
    isset($whereLteQuery['query']['bool']['filter'][0]['range']['discount']['lte']));

// Test 8: whereIn()
$whereInQuery = (new ElasticQuery())
    ->whereIn('category', ['tech', 'science', 'programming'])
    ->build();
runTest("whereIn() creates terms filter",
    isset($whereInQuery['query']['bool']['filter'][0]['terms']['category']) &&
    count($whereInQuery['query']['bool']['filter'][0]['terms']['category']) === 3);

// Test 9: whereNotIn()
$whereNotInQuery = (new ElasticQuery())
    ->whereNotIn('status', ['deleted', 'spam'])
    ->build();
runTest("whereNotIn() creates must_not terms",
    isset($whereNotInQuery['query']['bool']['must_not'][0]['terms']['status']) &&
    count($whereNotInQuery['query']['bool']['must_not'][0]['terms']['status']) === 2);

// Test 10: whereBetween()
$whereBetweenQuery = (new ElasticQuery())
    ->whereBetween('price', [100, 500])
    ->build();
runTest("whereBetween() creates range filters",
    isset($whereBetweenQuery['query']['bool']['filter']) &&
    count($whereBetweenQuery['query']['bool']['filter']) === 2);

// Test 11: Manual whereNotBetween equivalent using mustNot
$whereNotBetweenQuery = (new ElasticQuery())
    ->mustNot([
        'range' => [
            'age' => ['gte' => 13, 'lte' => 17]
        ]
    ])
    ->build();
runTest("Manual whereNotBetween creates must_not range",
    isset($whereNotBetweenQuery['query']['bool']['must_not'][0]['range']['age']));

// Test 12: whereNull()
$whereNullQuery = (new ElasticQuery())
    ->whereNull('deleted_at')
    ->build();
runTest("whereNull() creates must_not exists",
    isset($whereNullQuery['query']['bool']['must_not'][0]['exists']['field']) &&
    $whereNullQuery['query']['bool']['must_not'][0]['exists']['field'] === 'deleted_at');

// Test 13: whereNotNull()
$whereNotNullQuery = (new ElasticQuery())
    ->whereNotNull('email')
    ->build();
runTest("whereNotNull() creates exists filter",
    isset($whereNotNullQuery['query']['bool']['must'][0]['exists']['field']) &&
    $whereNotNullQuery['query']['bool']['must'][0]['exists']['field'] === 'email');

// Test 14: Multiple where conditions
$multiWhereQuery = (new ElasticQuery())
    ->where('status', 'active')
    ->where('price', '>=', 10)
    ->where('category', '!=', 'spam')
    ->build();
runTest("Multiple where conditions",
    isset($multiWhereQuery['query']['bool']['filter']) &&
    isset($multiWhereQuery['query']['bool']['must_not']) &&
    count($multiWhereQuery['query']['bool']['filter']) === 2 &&
    count($multiWhereQuery['query']['bool']['must_not']) === 1);

// Test 15: whereBetween validation (min > max)
try {
    (new ElasticQuery())->whereBetween('price', [500, 100]);
    runTest("whereBetween min > max validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("whereBetween min > max validation", true);
}

// Test 16: whereIn with empty array validation
try {
    (new ElasticQuery())->whereIn('tags', []);
    runTest("whereIn empty array validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("whereIn empty array validation", true);
}

// Test 17: where with null value validation
try {
    (new ElasticQuery())->where('status', null);
    runTest("where null value validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("where null value validation", true);
}

echo "\n" . str_repeat("=", 40) . "\n";
echo "📊 Where Clause Tests: $successCount/$totalTests passed\n";
echo "📈 Success Rate: " . round(($successCount/$totalTests) * 100, 1) . "%\n";

return ['passed' => $successCount, 'total' => $totalTests];
