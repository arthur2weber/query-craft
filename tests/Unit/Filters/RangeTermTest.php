<?php
/**
 * Range and Term Filter Tests
 * 
 * Tests range and term filter functionality:
 * - range() method with all operators
 * - rangeBoost() method
 * - term() method
 * - termBoost() method
 * - terms() method
 * - exists() method
 * 
 * @package Arthur2weber\QueryCraft\Tests\Unit\Filters
 */

require_once __DIR__ . '/../../../src/ElasticQueryInterface.php';
require_once __DIR__ . '/../../../src/ElasticQuery.php';

use Arthur2weber\QueryCraft\ElasticQuery;

echo "🧪 FILTERS: Range and Term Filter Tests\n";
echo str_repeat("=", 45) . "\n\n";

$successCount = 0;
$totalTests = 0;

function runTest($description, $result) {
    global $successCount, $totalTests;
    $totalTests++;
    echo ($result ? "✅" : "❌") . " $description\n";
    if ($result) $successCount++;
}

// Test 1: range() with 'gte' operator
$rangeGteQuery = (new ElasticQuery())
    ->range('age', 'gte', 18)
    ->build();
runTest("range() with gte operator",
    isset($rangeGteQuery['query']['bool']['filter'][0]['range']['age']['gte']) &&
    $rangeGteQuery['query']['bool']['filter'][0]['range']['age']['gte'] === 18);

// Test 2: range() with 'gt' operator
$rangeGtQuery = (new ElasticQuery())
    ->range('score', 'gt', 85)
    ->build();
runTest("range() with gt operator",
    isset($rangeGtQuery['query']['bool']['filter'][0]['range']['score']['gt']) &&
    $rangeGtQuery['query']['bool']['filter'][0]['range']['score']['gt'] === 85);

// Test 3: range() with 'lte' operator
$rangeLteQuery = (new ElasticQuery())
    ->range('price', 'lte', 1000)
    ->build();
runTest("range() with lte operator",
    isset($rangeLteQuery['query']['bool']['filter'][0]['range']['price']['lte']) &&
    $rangeLteQuery['query']['bool']['filter'][0]['range']['price']['lte'] === 1000);

// Test 4: range() with 'lt' operator
$rangeLtQuery = (new ElasticQuery())
    ->range('discount', 'lt', 50)
    ->build();
runTest("range() with lt operator",
    isset($rangeLtQuery['query']['bool']['filter'][0]['range']['discount']['lt']) &&
    $rangeLtQuery['query']['bool']['filter'][0]['range']['discount']['lt'] === 50);

// Test 5: rangeBoost() method
$rangeBoostQuery = (new ElasticQuery())
    ->rangeBoost('rating', 'gte', 4.5, 2.0)
    ->build();
runTest("rangeBoost() with boost value",
    isset($rangeBoostQuery['query']['bool']['must'][0]['range']['rating']['gte']) &&
    isset($rangeBoostQuery['query']['bool']['must'][0]['range']['rating']['boost']) &&
    $rangeBoostQuery['query']['bool']['must'][0]['range']['rating']['boost'] === 2.0);

// Test 6: Manual term filter using filter() method
$termQuery = (new ElasticQuery())
    ->filter('category', 'technology')
    ->build();
runTest("Manual term filter using filter()",
    isset($termQuery['query']['bool']['filter'][0]['term']['category']) &&
    $termQuery['query']['bool']['filter'][0]['term']['category'] === 'technology');

// Test 7: Manual term with boost using must()
$termBoostQuery = (new ElasticQuery())
    ->must(ElasticQuery::termBoostClause('featured', true, 1.5))
    ->build();
runTest("Manual term boost using termBoostClause()",
    isset($termBoostQuery['query']['bool']['must'][0]['term']['featured']['value']) &&
    isset($termBoostQuery['query']['bool']['must'][0]['term']['featured']['boost']) &&
    $termBoostQuery['query']['bool']['must'][0]['term']['featured']['boost'] === 1.5);

// Test 8: terms() method
$termsQuery = (new ElasticQuery())
    ->terms('tags', ['php', 'web', 'programming'])
    ->build();
runTest("terms() creates terms filter",
    isset($termsQuery['query']['bool']['filter'][0]['terms']['tags']) &&
    count($termsQuery['query']['bool']['filter'][0]['terms']['tags']) === 3);

// Test 9: exists() method
$existsQuery = (new ElasticQuery())
    ->exists('email')
    ->build();
runTest("exists() creates exists filter",
    isset($existsQuery['query']['bool']['must'][0]['exists']['field']) &&
    $existsQuery['query']['bool']['must'][0]['exists']['field'] === 'email');

// Test 10: Multiple range conditions on same field
$multiRangeQuery = (new ElasticQuery())
    ->range('price', 'gte', 100)
    ->range('price', 'lte', 500)
    ->build();
runTest("Multiple range conditions on same field",
    isset($multiRangeQuery['query']['bool']['filter']) &&
    count($multiRangeQuery['query']['bool']['filter']) === 2);

// Test 11: Range with string values (dates)
$dateRangeQuery = (new ElasticQuery())
    ->range('created_at', 'gte', '2024-01-01')
    ->build();
runTest("Range with string values (dates)",
    isset($dateRangeQuery['query']['bool']['filter'][0]['range']['created_at']['gte']) &&
    $dateRangeQuery['query']['bool']['filter'][0]['range']['created_at']['gte'] === '2024-01-01');

// Test 12: Terms with mixed value types
$mixedTermsQuery = (new ElasticQuery())
    ->terms('mixed_field', ['string', 123, true])
    ->build();
runTest("Terms with mixed value types",
    isset($mixedTermsQuery['query']['bool']['filter'][0]['terms']['mixed_field']) &&
    count($mixedTermsQuery['query']['bool']['filter'][0]['terms']['mixed_field']) === 3);

// Test 13: Invalid range operator validation
try {
    (new ElasticQuery())->range('field', 'invalid_op', 100);
    runTest("Invalid range operator validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Invalid range operator validation", true);
}

// Test 14: Terms with empty array validation
try {
    (new ElasticQuery())->terms('tags', []);
    runTest("Terms empty array validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Terms empty array validation", true);
}

// Test 15: Range with infinite values validation
try {
    (new ElasticQuery())->range('field', 'gte', INF);
    runTest("Range infinite values validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Range infinite values validation", true);
}

// Test 16: Complex filter combination
$complexFilterQuery = (new ElasticQuery())
    ->filter('status', 'active')
    ->range('price', 'gte', 10)
    ->range('price', 'lte', 1000)
    ->terms('categories', ['tech', 'science'])
    ->exists('description')
    ->build();
runTest("Complex filter combination",
    isset($complexFilterQuery['query']['bool']['filter']) &&
    isset($complexFilterQuery['query']['bool']['must']) &&
    count($complexFilterQuery['query']['bool']['filter']) === 4 &&
    count($complexFilterQuery['query']['bool']['must']) === 1);

echo "\n" . str_repeat("=", 45) . "\n";
echo "📊 Range and Term Filter Tests: $successCount/$totalTests passed\n";
echo "📈 Success Rate: " . round(($successCount/$totalTests) * 100, 1) . "%\n";

return ['passed' => $successCount, 'total' => $totalTests];
