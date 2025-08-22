<?php
/**
 * Boolean Query Tests
 * 
 * Tests boolean query functionality:
 * - must() method
 * - should() method  
 * - mustNot() method
 * - filter() method
 * - minimumShouldMatch() method
 * 
 * @package Arthur2weber\QueryCraft\Tests\Unit\Core
 */

require_once __DIR__ . '/../../../../src/ElasticQueryInterface.php';
require_once __DIR__ . '/../../../../src/ElasticQuery.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\ElasticQuery;

echo "🧪 CORE: Boolean Query Tests\n";
echo str_repeat("=", 40) . "\n\n";

resetTestCounters();

// Test 1: must() method
$mustQuery = (new ElasticQuery())
    ->must(['term' => ['status' => 'active']])
    ->build();
runTest("must() adds condition to must array", 
    isset($mustQuery['query']['bool']['must'][0]['term']['status']));

// Test 2: should() method
$shouldQuery = (new ElasticQuery())
    ->should(['term' => ['featured' => true]])
    ->build();
runTest("should() adds condition to should array",
    isset($shouldQuery['query']['bool']['should'][0]['term']['featured']));

// Test 3: mustNot() method
$mustNotQuery = (new ElasticQuery())
    ->mustNot(['term' => ['deleted' => true]])
    ->build();
runTest("mustNot() adds condition to must_not array",
    isset($mustNotQuery['query']['bool']['must_not'][0]['term']['deleted']));

// Test 4: filter() method with field/value
$filterQuery = (new ElasticQuery())
    ->filter('category', 'tech')
    ->build();
runTest("filter() creates term filter",
    isset($filterQuery['query']['bool']['filter'][0]['term']['category']));

// Test 5: filter() method with array condition
$filterArrayQuery = (new ElasticQuery())
    ->filter(['range' => ['price' => ['gte' => 100]]])
    ->build();
runTest("filter() accepts array condition",
    isset($filterArrayQuery['query']['bool']['filter'][0]['range']['price']));

// Test 6: Multiple must conditions
$multiMustQuery = (new ElasticQuery())
    ->must(['term' => ['status' => 'active']])
    ->must(['term' => ['published' => true]])
    ->build();
runTest("Multiple must conditions",
    count($multiMustQuery['query']['bool']['must']) === 2);

// Test 7: Multiple should conditions
$multiShouldQuery = (new ElasticQuery())
    ->should(['term' => ['priority' => 'high']])
    ->should(['term' => ['urgent' => true]])
    ->build();
runTest("Multiple should conditions",
    count($multiShouldQuery['query']['bool']['should']) === 2);

// Test 8: minimumShouldMatch with integer
$msmIntQuery = (new ElasticQuery())
    ->should(['term' => ['tag1' => 'value1']])
    ->should(['term' => ['tag2' => 'value2']])
    ->minimumShouldMatch(1)
    ->build();
runTest("minimumShouldMatch with integer",
    isset($msmIntQuery['query']['bool']['minimum_should_match']) &&
    $msmIntQuery['query']['bool']['minimum_should_match'] === 1);

// Test 9: minimumShouldMatch with percentage
$msmPercentQuery = (new ElasticQuery())
    ->should(['term' => ['tag1' => 'value1']])
    ->should(['term' => ['tag2' => 'value2']])
    ->minimumShouldMatch('50%')
    ->build();
runTest("minimumShouldMatch with percentage",
    isset($msmPercentQuery['query']['bool']['minimum_should_match']) &&
    $msmPercentQuery['query']['bool']['minimum_should_match'] === '50%');

// Test 10: Complex boolean combination
$complexBoolQuery = (new ElasticQuery())
    ->must(['term' => ['status' => 'active']])
    ->should(['term' => ['featured' => true]])
    ->should(['term' => ['trending' => true]])
    ->mustNot(['term' => ['spam' => true]])
    ->filter('category', 'news')
    ->minimumShouldMatch(1)
    ->build();
runTest("Complex boolean combination",
    isset($complexBoolQuery['query']['bool']['must']) &&
    isset($complexBoolQuery['query']['bool']['should']) &&
    isset($complexBoolQuery['query']['bool']['must_not']) &&
    isset($complexBoolQuery['query']['bool']['filter']) &&
    isset($complexBoolQuery['query']['bool']['minimum_should_match']));

// Test 11: Empty condition validation
try {
    (new ElasticQuery())->must([]);
    runTest("Empty must condition validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Empty must condition validation", true);
}

// Test 12: Null filter value validation
try {
    (new ElasticQuery())->filter('status', null);
    runTest("Null filter value validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Null filter value validation", true);
}

return printTestSummary("Boolean Query Tests");
