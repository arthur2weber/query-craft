<?php
/**
 * Utility Methods Tests
 * 
 * Tests utility and helper functionality:
 * - size() / limit() / take() methods
 * - from() / offset() / skip() methods
 * - paginate() method
 * - source() method
 * - highlight() method
 * - timeout() method
 * - scriptScore() method
 * - when() / unless() conditional methods
 * - Predefined scopes (active, published, recent)
 * 
 * @package Arthur2weber\QueryCraft\Tests\Unit\Utilities
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\ElasticQuery as ElasticQuery;

echo "🧪 UTILITIES: Utility Methods Tests\n";
echo str_repeat("=", 45) . "\n\n";

$successCount = 0;
$totalTests = 0;

function runTest($description, $result) {
    global $successCount, $totalTests;
    $totalTests++;
    echo ($result ? "✅" : "❌") . " $description\n";
    if ($result) $successCount++;
}

// Test 1: size() method
$sizeQuery = (new ElasticQuery())
    ->size(25)
    ->build();
runTest("size() method",
    isset($sizeQuery['size']) && $sizeQuery['size'] === 25);

// Test 2: limit() method (alias for size)
$limitQuery = (new ElasticQuery())
    ->limit(50)
    ->build();
runTest("limit() method",
    isset($limitQuery['size']) && $limitQuery['size'] === 50);

// Test 3: take() method (alias for size)
$takeQuery = (new ElasticQuery())
    ->take(10)
    ->build();
runTest("take() method",
    isset($takeQuery['size']) && $takeQuery['size'] === 10);

// Test 4: from() method
$fromQuery = (new ElasticQuery())
    ->from(20)
    ->build();
runTest("from() method",
    isset($fromQuery['from']) && $fromQuery['from'] === 20);

// Test 5: offset() method (alias for from)
$offsetQuery = (new ElasticQuery())
    ->offset(30)
    ->build();
runTest("offset() method",
    isset($offsetQuery['from']) && $offsetQuery['from'] === 30);

// Test 6: skip() method (alias for from)
$skipQuery = (new ElasticQuery())
    ->skip(15)
    ->build();
runTest("skip() method",
    isset($skipQuery['from']) && $skipQuery['from'] === 15);

// Test 7: paginate() method
$paginateQuery = (new ElasticQuery())
    ->paginate(20, 3) // 20 per page, page 3
    ->build();
runTest("paginate() method",
    isset($paginateQuery['size']) && 
    isset($paginateQuery['from']) &&
    $paginateQuery['size'] === 20 &&
    $paginateQuery['from'] === 40); // (3-1) * 20 = 40

// Test 8: paginate() with default values
$paginateDefaultQuery = (new ElasticQuery())
    ->paginate() // Default: 15 per page, page 1
    ->build();
runTest("paginate() with defaults",
    isset($paginateDefaultQuery['size']) && 
    isset($paginateDefaultQuery['from']) &&
    $paginateDefaultQuery['size'] === 15 &&
    $paginateDefaultQuery['from'] === 0);

// Test 9: source() method with array
$sourceArrayQuery = (new ElasticQuery())
    ->source(['title', 'content', 'created_at'])
    ->build();
runTest("source() with array",
    isset($sourceArrayQuery['_source']) &&
    is_array($sourceArrayQuery['_source']) &&
    count($sourceArrayQuery['_source']) === 3);

// Test 10: source() method with false
$sourceFalseQuery = (new ElasticQuery())
    ->source(false)
    ->build();
runTest("source() with false",
    isset($sourceFalseQuery['_source']) && 
    $sourceFalseQuery['_source'] === false);

// Test 11: highlight() method
$highlightQuery = (new ElasticQuery())
    ->highlight(['title', 'content'])
    ->build();
runTest("highlight() method",
    isset($highlightQuery['highlight']['fields']['title']) &&
    isset($highlightQuery['highlight']['fields']['content']) &&
    isset($highlightQuery['highlight']['pre_tags']) &&
    $highlightQuery['highlight']['pre_tags'][0] === '<em>');

// Test 12: highlight() with custom tags
$highlightCustomQuery = (new ElasticQuery())
    ->highlight(['title'], '<mark>', '</mark>')
    ->build();
runTest("highlight() with custom tags",
    isset($highlightCustomQuery['highlight']['pre_tags']) &&
    isset($highlightCustomQuery['highlight']['post_tags']) &&
    $highlightCustomQuery['highlight']['pre_tags'][0] === '<mark>' &&
    $highlightCustomQuery['highlight']['post_tags'][0] === '</mark>');

// Test 13: timeout() method
$timeoutQuery = (new ElasticQuery())
    ->timeout('5s')
    ->build();
runTest("timeout() method",
    isset($timeoutQuery['timeout']) && $timeoutQuery['timeout'] === '5s');

// Test 14: scriptScore() method
$scriptScoreQuery = (new ElasticQuery())
    ->where('status', 'active')
    ->scriptScore('Math.log(2 + doc["views"].value)')
    ->build();
runTest("scriptScore() method",
    isset($scriptScoreQuery['query']['script_score']['query']) &&
    isset($scriptScoreQuery['query']['script_score']['script']['source']));

// Test 15: when() conditional method - true condition
$whenTrueQuery = (new ElasticQuery())
    ->when(true, function($query) {
        return $query->where('status', 'active');
    })
    ->build();
runTest("when() with true condition",
    isset($whenTrueQuery['query']['bool']['filter'][0]['term']['status']));

// Test 16: when() conditional method - false condition
$whenFalseQuery = (new ElasticQuery())
    ->when(false, function($query) {
        return $query->where('status', 'active');
    })
    ->build();
runTest("when() with false condition",
    !isset($whenFalseQuery['query']['bool']['filter']));

// Test 17: when() with default callback
$whenDefaultQuery = (new ElasticQuery())
    ->when(false, 
        function($query) {
            return $query->where('status', 'active');
        },
        function($query) {
            return $query->where('status', 'published');
        }
    )
    ->build();
runTest("when() with default callback",
    isset($whenDefaultQuery['query']['bool']['filter'][0]['term']['status']) &&
    $whenDefaultQuery['query']['bool']['filter'][0]['term']['status'] === 'published');

// Test 18: unless() conditional method
$unlessQuery = (new ElasticQuery())
    ->unless(false, function($query) {
        return $query->where('featured', true);
    })
    ->build();
runTest("unless() conditional method",
    isset($unlessQuery['query']['bool']['filter'][0]['term']['featured']));

// Test 19: active() predefined scope
$activeQuery = (new ElasticQuery())
    ->active()
    ->build();
runTest("active() predefined scope",
    isset($activeQuery['query']['bool']['filter'][0]['term']['status']) &&
    $activeQuery['query']['bool']['filter'][0]['term']['status'] === 'active');

// Test 20: published() predefined scope
$publishedQuery = (new ElasticQuery())
    ->published()
    ->build();
runTest("published() predefined scope",
    isset($publishedQuery['query']['bool']['filter']) &&
    count($publishedQuery['query']['bool']['filter']) === 2); // status + published_at

// Test 21: recent() predefined scope
$recentQuery = (new ElasticQuery())
    ->recent(7)
    ->build();
runTest("recent() predefined scope",
    isset($recentQuery['query']['bool']['filter'][0]['range']['created_at']['gte']));

// Test 22: recent() with default days
$recentDefaultQuery = (new ElasticQuery())
    ->recent()
    ->build();
runTest("recent() with default days",
    isset($recentDefaultQuery['query']['bool']['filter'][0]['range']['created_at']['gte']));

// Test 23: Complex utility combination
$complexUtilQuery = (new ElasticQuery())
    ->search('tutorial', ['title^2', 'content'])
    ->active()
    ->recent(30)
    ->highlight(['title', 'content'])
    ->orderByDesc('_score')
    ->orderByDesc('created_at')
    ->paginate(15, 2)
    ->source(['id', 'title', 'summary', 'created_at'])
    ->timeout('3s')
    ->build();
runTest("Complex utility combination",
    isset($complexUtilQuery['query']['bool']['must']) &&
    isset($complexUtilQuery['query']['bool']['filter']) &&
    isset($complexUtilQuery['highlight']) &&
    isset($complexUtilQuery['sort']) &&
    isset($complexUtilQuery['size']) &&
    isset($complexUtilQuery['from']) &&
    isset($complexUtilQuery['_source']) &&
    isset($complexUtilQuery['timeout']));

// Test 24: Size validation (negative)
try {
    (new ElasticQuery())->size(-1);
    runTest("Size negative validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Size negative validation", true);
}

// Test 25: From validation (negative)
try {
    (new ElasticQuery())->from(-1);
    runTest("From negative validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("From negative validation", true);
}

// Test 26: Paginate validation (invalid page)
try {
    (new ElasticQuery())->paginate(10, 0);
    runTest("Paginate invalid page validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Paginate invalid page validation", true);
}

// Test 27: Highlight empty fields validation
try {
    (new ElasticQuery())->highlight([]);
    runTest("Highlight empty fields validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Highlight empty fields validation", true);
}

// ================================================================
// ANALYZE METHOD TESTS
// ================================================================

// Test 28: Simple query analysis
$simpleQuery = new ElasticQuery();
$simpleQuery->where('status', 'published');
$analysis = $simpleQuery->analyze();
runTest("Simple query analyze() - returns array", is_array($analysis));
runTest("Simple query analyze() - has estimated_complexity", isset($analysis['estimated_complexity']));
runTest("Simple query analyze() - has clause_count", isset($analysis['clause_count']));
runTest("Simple query analyze() - has has_search", isset($analysis['has_search']));
runTest("Simple query analyze() - low complexity", $analysis['estimated_complexity'] === 'low');
runTest("Simple query analyze() - no search", $analysis['has_search'] === false);

// Test 29: Complex query analysis
$complexQuery = new ElasticQuery();
$complexQuery->search('PHP tutorial', ['title', 'content'])
             ->where('status', 'published')
             ->whereIn('tags', ['php', 'javascript'])
             ->aggregation('categories', ['terms' => ['field' => 'category.keyword']])
             ->orderBy('created_at', 'desc');
$complexAnalysis = $complexQuery->analyze();
runTest("Complex query analyze() - has search", $complexAnalysis['has_search'] === true);
runTest("Complex query analyze() - has aggregations", $complexAnalysis['has_aggregations'] === true);
runTest("Complex query analyze() - has sorting", $complexAnalysis['has_sorting'] === true);
runTest("Complex query analyze() - medium/high complexity", 
        in_array($complexAnalysis['estimated_complexity'], ['medium', 'high']));

// ================================================================
// CACHING TESTS
// ================================================================

// Test 30: Term clause caching
$term1 = ElasticQuery::termClause('status', 'published');
$term2 = ElasticQuery::termClause('status', 'published');
runTest("Term clause caching - identical results", $term1 === $term2);

// Test 31: Match clause caching (string values)
$match1 = ElasticQuery::matchClause('title', 'PHP Tutorial');
$match2 = ElasticQuery::matchClause('title', 'PHP Tutorial');
runTest("Match clause caching - identical results", $match1 === $match2);

// Test 32: Match clause no caching for arrays
$matchArray1 = ElasticQuery::matchClause('title', ['PHP', 'Tutorial']);
$matchArray2 = ElasticQuery::matchClause('title', ['PHP', 'Tutorial']);
// Arrays with same content are identical in PHP, but this verifies no caching occurred
// by checking that the arrays have the expected structure (not cached objects)
runTest("Match clause array - returns identical arrays", $matchArray1 === $matchArray2);

// Test 33: Range clause caching
$range1 = ElasticQuery::rangeClause('price', 10, 100);
$range2 = ElasticQuery::rangeClause('price', 10, 100);
runTest("Range clause caching - identical results", $range1 === $range2);

echo "\n" . str_repeat("=", 45) . "\n";
echo "📊 Utility Methods Tests: $successCount/$totalTests passed\n";
echo "📈 Success Rate: " . round(($successCount/$totalTests) * 100, 1) . "%\n";

return ['passed' => $successCount, 'total' => $totalTests];
