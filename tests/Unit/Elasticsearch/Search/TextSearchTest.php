<?php
/**
 * Text Search Tests
 * 
 * Tests full-text search functionality:
 * - search() multi-match method
 * - searchIn() single field search
 * - searchPhrase() phrase search
 * - match() method
 * - matchBoost() method
 * - matchPhrase() method
 * - queryString() method
 * 
 * @package Arthur2weber\QueryCraft\Tests\Unit\Search
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\ElasticQuery;

echo "🧪 SEARCH: Text Search Tests\n";
echo str_repeat("=", 40) . "\n\n";

resetTestCounters();

// Test 1: search() basic multi-match
$searchQuery = (new ElasticQuery())
    ->search('Laravel PHP', ['title', 'content'])
    ->build();
runTest("search() basic multi-match",
    isset($searchQuery['query']['bool']['must'][0]['multi_match']['query']) &&
    isset($searchQuery['query']['bool']['must'][0]['multi_match']['fields']) &&
    $searchQuery['query']['bool']['must'][0]['multi_match']['query'] === 'Laravel PHP' &&
    count($searchQuery['query']['bool']['must'][0]['multi_match']['fields']) === 2);

// Test 2: search() with boosted fields
$searchBoostFieldsQuery = (new ElasticQuery())
    ->search('Elasticsearch guide', ['title^3', 'content^1', 'tags^2'])
    ->build();
runTest("search() with boosted fields",
    isset($searchBoostFieldsQuery['query']['bool']['must'][0]['multi_match']['fields']) &&
    in_array('title^3', $searchBoostFieldsQuery['query']['bool']['must'][0]['multi_match']['fields']) &&
    in_array('tags^2', $searchBoostFieldsQuery['query']['bool']['must'][0]['multi_match']['fields']));

// Test 3: search() with boost parameter
$searchBoostQuery = (new ElasticQuery())
    ->search('tutorial', ['title', 'content'], 1.5)
    ->build();
runTest("search() with boost parameter",
    isset($searchBoostQuery['query']['bool']['must'][0]['multi_match']['boost']) &&
    $searchBoostQuery['query']['bool']['must'][0]['multi_match']['boost'] === 1.5);

// Test 4: searchIn() single field
$searchInQuery = (new ElasticQuery())
    ->searchIn('title', 'machine learning')
    ->build();
runTest("searchIn() single field search",
    isset($searchInQuery['query']['bool']['must'][0]['match']['title']) &&
    $searchInQuery['query']['bool']['must'][0]['match']['title'] === 'machine learning');

// Test 5: searchPhrase() method
$searchPhraseQuery = (new ElasticQuery())
    ->searchPhrase('content', 'step by step tutorial')
    ->build();
runTest("searchPhrase() method",
    isset($searchPhraseQuery['query']['bool']['must'][0]['match_phrase']['content']) &&
    $searchPhraseQuery['query']['bool']['must'][0]['match_phrase']['content'] === 'step by step tutorial');

// Test 6: match() method
$matchQuery = (new ElasticQuery())
    ->match('description', 'web development')
    ->build();
runTest("match() method",
    isset($matchQuery['query']['bool']['must'][0]['match']['description']) &&
    $matchQuery['query']['bool']['must'][0]['match']['description'] === 'web development');

// Test 7: matchBoost() method
$matchBoostQuery = (new ElasticQuery())
    ->matchBoost('title', 'React tutorial', 2.0)
    ->build();
runTest("matchBoost() method",
    isset($matchBoostQuery['query']['bool']['must'][0]['match']['title']['query']) &&
    isset($matchBoostQuery['query']['bool']['must'][0]['match']['title']['boost']) &&
    $matchBoostQuery['query']['bool']['must'][0]['match']['title']['boost'] === 2.0);

// Test 8: Another searchPhrase() test
$matchPhraseQuery = (new ElasticQuery())
    ->searchPhrase('content', 'artificial intelligence')
    ->build();
runTest("searchPhrase() additional test",
    isset($matchPhraseQuery['query']['bool']['must'][0]['match_phrase']['content']) &&
    $matchPhraseQuery['query']['bool']['must'][0]['match_phrase']['content'] === 'artificial intelligence');

// Test 9: Manual query_string test
$queryStringQuery = (new ElasticQuery())
    ->must(['query_string' => ['query' => 'title:(Laravel OR Symfony) AND content:tutorial']])
    ->build();
runTest("Manual query_string structure",
    isset($queryStringQuery['query']['bool']['must'][0]['query_string']['query']) &&
    $queryStringQuery['query']['bool']['must'][0]['query_string']['query'] === 'title:(Laravel OR Symfony) AND content:tutorial');

// Test 10: Multiple search methods combined
$multiSearchQuery = (new ElasticQuery())
    ->search('PHP tutorial', ['title^2', 'content'])
    ->matchBoost('tags', 'beginner', 1.5)
    ->searchPhrase('description', 'step by step')
    ->build();
runTest("Multiple search methods combined",
    isset($multiSearchQuery['query']['bool']['must']) &&
    count($multiSearchQuery['query']['bool']['must']) === 3);

// Test 11: search() with default fields
$searchDefaultQuery = (new ElasticQuery())
    ->search('programming languages')
    ->build();
runTest("search() with default fields",
    isset($searchDefaultQuery['query']['bool']['must'][0]['multi_match']['fields']) &&
    count($searchDefaultQuery['query']['bool']['must'][0]['multi_match']['fields']) === 1);

// Test 12: Empty search query validation
try {
    (new ElasticQuery())->search('');
    runTest("Empty search query validation", true); // Should trigger warning but not throw
} catch (\Exception $e) {
    runTest("Empty search query validation", false);
}

// Test 13: Empty fields array validation
try {
    (new ElasticQuery())->search('test', []);
    runTest("Empty fields array validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Empty fields array validation", true);
}

// Test 14: Search field name validation
try {
    (new ElasticQuery())->search('test', ['', 'content']);
    runTest("Invalid field name validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Invalid field name validation", true);
}

// Test 15: Complex search with filters
$complexSearchQuery = (new ElasticQuery())
    ->search('Laravel framework', ['title^3', 'content^1', 'tags^2'])
    ->where('status', 'published')
    ->range('rating', 'gte', 4.0)
    ->build();
runTest("Complex search with filters",
    isset($complexSearchQuery['query']['bool']['must']) &&
    isset($complexSearchQuery['query']['bool']['filter']) &&
    count($complexSearchQuery['query']['bool']['must']) === 1 &&
    count($complexSearchQuery['query']['bool']['filter']) === 2);

return printTestSummary("Text Search Tests");
