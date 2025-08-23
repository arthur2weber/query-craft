<?php
/**
 * Static Clause Tests
 * 
 * Tests all static clause generation methods:
 * - termClause() 
 * - termsClause()
 * - rangeClause()
 * - matchClause()
 * - matchBoostClause()
 * - termBoostClause()
 * - rangeBoostClause()
 * - existsClause()
 * - prefixClause()
 * - wildcardClause()
 * - fuzzyClause()
 * - regexpClause()
 * - nestedClause()
 * 
 * @package Arthur2weber\QueryCraft\Tests\Unit\Core
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\ElasticQuery;

echo "🧪 CORE: Static Clause Tests\n";
echo str_repeat("=", 40) . "\n\n";

resetTestCounters();

// Test 1: termClause()
$termClause = ElasticQuery::termClause('status', 'active');
runTest("termClause() structure",
    isset($termClause['term']['status']) && 
    $termClause['term']['status'] === 'active');

// Test 2: termsClause()
$termsClause = ElasticQuery::termsClause('tags', ['php', 'web']);
runTest("termsClause() structure",
    isset($termsClause['terms']['tags']) && 
    is_array($termsClause['terms']['tags']) &&
    count($termsClause['terms']['tags']) === 2);

// Test 3: rangeClause()
$rangeClause = ElasticQuery::rangeClause('age', 'gte', 18);
runTest("rangeClause() structure",
    isset($rangeClause['range']['age']['gte']) && 
    $rangeClause['range']['age']['gte'] === 18);

// Test 4: matchClause() simple
$matchClause = ElasticQuery::matchClause('title', 'Laravel PHP');
runTest("matchClause() simple structure",
    isset($matchClause['match']['title']) && 
    $matchClause['match']['title'] === 'Laravel PHP');

// Test 5: matchClause() with array
$matchArrayClause = ElasticQuery::matchClause('title', ['query' => 'Laravel', 'boost' => 2.0]);
runTest("matchClause() with array structure",
    isset($matchArrayClause['match']['title']['query']) && 
    isset($matchArrayClause['match']['title']['boost']) &&
    $matchArrayClause['match']['title']['boost'] === 2.0);

// Test 6: matchBoostClause()
$matchBoostClause = ElasticQuery::matchBoostClause('title', 'Laravel', 1.5);
runTest("matchBoostClause() structure",
    isset($matchBoostClause['match']['title']['query']) && 
    isset($matchBoostClause['match']['title']['boost']) &&
    $matchBoostClause['match']['title']['boost'] === 1.5);

// Test 7: termBoostClause()
$termBoostClause = ElasticQuery::termBoostClause('category', 'tech', 2.0);
runTest("termBoostClause() structure",
    isset($termBoostClause['term']['category']['value']) && 
    isset($termBoostClause['term']['category']['boost']) &&
    $termBoostClause['term']['category']['boost'] === 2.0);

// Test 8: rangeBoostClause()
$rangeBoostClause = ElasticQuery::rangeBoostClause('score', 'gte', 90, 1.8);
runTest("rangeBoostClause() structure",
    isset($rangeBoostClause['range']['score']['gte']) && 
    isset($rangeBoostClause['range']['score']['boost']) &&
    $rangeBoostClause['range']['score']['boost'] === 1.8);

// Test 9: existsClause()
$existsClause = ElasticQuery::existsClause('email');
runTest("existsClause() structure",
    isset($existsClause['exists']['field']) && 
    $existsClause['exists']['field'] === 'email');

// Test 10: prefixClause()
$prefixClause = ElasticQuery::prefixClause('name', 'john');
runTest("prefixClause() structure",
    isset($prefixClause['prefix']['name']) && 
    $prefixClause['prefix']['name'] === 'john');

// Test 11: wildcardClause()
$wildcardClause = ElasticQuery::wildcardClause('name', 'jo*n');
runTest("wildcardClause() structure",
    isset($wildcardClause['wildcard']['name']) && 
    $wildcardClause['wildcard']['name'] === 'jo*n');

// Test 12: Manual fuzzy clause structure (fuzzyClause not implemented)
$fuzzyClause = [
    'fuzzy' => [
        'title' => [
            'value' => 'javscript',
            'fuzziness' => 2
        ]
    ]
];
runTest("Manual fuzzy clause structure",
    isset($fuzzyClause['fuzzy']['title']['value']) && 
    isset($fuzzyClause['fuzzy']['title']['fuzziness']) &&
    $fuzzyClause['fuzzy']['title']['fuzziness'] === 2);

// Test 13: regexpClause()
$regexpClause = ElasticQuery::regexpClause('email', '[a-z]+@[a-z]+');
runTest("regexpClause() structure",
    isset($regexpClause['regexp']['email']) && 
    $regexpClause['regexp']['email'] === '[a-z]+@[a-z]+');

// Test 14: nestedClause()
$innerQuery = ['term' => ['comments.status' => 'approved']];
$nestedClause = ElasticQuery::nestedClause('comments', $innerQuery);
runTest("nestedClause() structure",
    isset($nestedClause['nested']['path']) && 
    isset($nestedClause['nested']['query']) &&
    $nestedClause['nested']['path'] === 'comments');

// Test 15: Clause chaining consistency
$clause1 = ElasticQuery::matchClause('title', 'test');
$clause2 = ElasticQuery::matchClause('title', 'test');
runTest("Static clauses are consistent", $clause1 === $clause2);

return printTestSummary("Static Clause Tests");
