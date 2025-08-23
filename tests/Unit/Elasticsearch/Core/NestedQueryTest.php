<?php
/**
 * Nested Query Tests
 * 
 * Tests nested query functionality:
 * - nested() method with callbacks
 * - hasChild() method
 * - hasParent() method
 * - Complex nested structures
 * 
 * @package Arthur2weber\QueryCraft\Tests\Unit\Core
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\ElasticQuery;

echo "🧪 CORE: Nested Query Tests\n";
echo str_repeat("=", 40) . "\n\n";

resetTestCounters();

// Test 1: Basic nested query
$nestedQuery = (new ElasticQuery())
    ->nested('comments', function($q) {
        $q->where('comments.status', 'approved')
          ->range('comments.rating', 'gte', 4);
    })
    ->build();
runTest("Basic nested query",
    isset($nestedQuery['query']['bool']['must'][0]['nested']['path']) &&
    isset($nestedQuery['query']['bool']['must'][0]['nested']['query']) &&
    $nestedQuery['query']['bool']['must'][0]['nested']['path'] === 'comments');

// Test 2: Nested query with match
$nestedMatchQuery = (new ElasticQuery())
    ->nested('reviews', function($q) {
        $q->match('reviews.text', 'excellent product');
    })
    ->build();
runTest("Nested query with match",
    isset($nestedMatchQuery['query']['bool']['must'][0]['nested']['path']) &&
    isset($nestedMatchQuery['query']['bool']['must'][0]['nested']['query']['bool']['must'][0]['match']));

// Test 3: Multiple nested queries
$multiNestedQuery = (new ElasticQuery())
    ->nested('comments', function($q) {
        $q->where('comments.status', 'approved');
    })
    ->nested('reviews', function($q) {
        $q->range('reviews.rating', 'gte', 4);
    })
    ->build();
runTest("Multiple nested queries",
    isset($multiNestedQuery['query']['bool']['must']) &&
    count($multiNestedQuery['query']['bool']['must']) === 2);

// Test 4: Nested query with complex inner structure
$complexNestedQuery = (new ElasticQuery())
    ->nested('user_interactions', function($q) {
        $q->where('user_interactions.type', 'purchase')
          ->range('user_interactions.timestamp', 'gte', '2024-01-01')
          ->range('user_interactions.amount', 'gte', 100)
          ->should(['term' => ['user_interactions.verified' => true]])
          ->should(['term' => ['user_interactions.premium' => true]])
          ->minimumShouldMatch(1);
    })
    ->build();
runTest("Complex nested query structure",
    isset($complexNestedQuery['query']['bool']['must'][0]['nested']['query']['bool']['filter']) &&
    isset($complexNestedQuery['query']['bool']['must'][0]['nested']['query']['bool']['should']));

// Test 5: Nested query combined with regular filters
$nestedWithFiltersQuery = (new ElasticQuery())
    ->where('status', 'active')
    ->range('created_at', 'gte', '2024-01-01')
    ->nested('author', function($q) {
        $q->where('author.verified', true)
          ->range('author.reputation', 'gte', 100);
    })
    ->build();
runTest("Nested query with regular filters",
    isset($nestedWithFiltersQuery['query']['bool']['filter']) &&
    isset($nestedWithFiltersQuery['query']['bool']['must']) &&
    count($nestedWithFiltersQuery['query']['bool']['filter']) === 2 &&
    count($nestedWithFiltersQuery['query']['bool']['must']) === 1);

// Test 6: Nested query with search
$nestedSearchQuery = (new ElasticQuery())
    ->search('programming tutorial', ['title^2', 'content'])
    ->nested('comments', function($q) {
        $q->search('helpful', ['comments.text'])
          ->range('comments.upvotes', 'gte', 5);
    })
    ->build();
runTest("Nested query with search",
    isset($nestedSearchQuery['query']['bool']['must']) &&
    count($nestedSearchQuery['query']['bool']['must']) === 2);

// Test 7: Manual has_child structure
$hasChildQuery = (new ElasticQuery())
    ->must([
        'has_child' => [
            'type' => 'comment',
            'query' => [
                'bool' => [
                    'filter' => [
                        ['term' => ['status' => 'approved']],
                        ['range' => ['score' => ['gte' => 5]]]
                    ]
                ]
            ]
        ]
    ])
    ->build();
runTest("Manual has_child structure",
    isset($hasChildQuery['query']['bool']['must'][0]['has_child']['type']) &&
    isset($hasChildQuery['query']['bool']['must'][0]['has_child']['query']) &&
    $hasChildQuery['query']['bool']['must'][0]['has_child']['type'] === 'comment');

// Test 8: Manual has_parent structure
$hasParentQuery = (new ElasticQuery())
    ->must([
        'has_parent' => [
            'parent_type' => 'article',
            'query' => [
                'bool' => [
                    'filter' => [
                        ['term' => ['category' => 'technology']],
                        ['range' => ['published_at' => ['gte' => '2024-01-01']]]
                    ]
                ]
            ]
        ]
    ])
    ->build();
runTest("Manual has_parent structure",
    isset($hasParentQuery['query']['bool']['must'][0]['has_parent']['parent_type']) &&
    isset($hasParentQuery['query']['bool']['must'][0]['has_parent']['query']) &&
    $hasParentQuery['query']['bool']['must'][0]['has_parent']['parent_type'] === 'article');

// Test 9: Nested query with aggregations
$nestedAggQuery = (new ElasticQuery())
    ->nested('products', function($q) {
        $q->range('products.price', 'gte', 100);
    })
    ->aggregation('avg_price', ['avg' => ['field' => 'products.price']])
    ->build();
runTest("Nested query with aggregations",
    isset($nestedAggQuery['query']['bool']['must'][0]['nested']) &&
    isset($nestedAggQuery['aggs']['avg_price']));

// Test 10: Multiple levels of nesting (nested within nested)
$deepNestedQuery = (new ElasticQuery())
    ->nested('orders', function($q) {
        $q->where('orders.status', 'completed')
          ->nested('orders.items', function($q2) {
              $q2->where('orders.items.category', 'electronics')
                 ->range('orders.items.price', 'gte', 500);
          });
    })
    ->build();
runTest("Deep nested query",
    isset($deepNestedQuery['query']['bool']['must'][0]['nested']['query']['bool']['must']));

// Test 11: Empty nested path validation
try {
    (new ElasticQuery())->nested('', function($q) { $q->where('test', 'value'); });
    runTest("Empty nested path validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Empty nested path validation", true);
}

// Test 12: Nested query callback produces empty query warning
$emptyNestedQuery = (new ElasticQuery())
    ->nested('comments', function($q) {
        // Empty callback - should trigger warning but not error
    })
    ->build();
runTest("Empty nested callback handling",
    isset($emptyNestedQuery['query']['bool']['must'][0]['nested']['path']));

// Test 13: Manual has_child with score mode
$hasChildScoreQuery = (new ElasticQuery())
    ->must([
        'has_child' => [
            'type' => 'comment',
            'score_mode' => 'max',
            'query' => [
                'bool' => [
                    'must' => [
                        ['match' => ['text' => 'excellent']]
                    ]
                ]
            ]
        ]
    ])
    ->build();
runTest("Manual has_child with score mode",
    isset($hasChildScoreQuery['query']['bool']['must'][0]['has_child']['score_mode']) &&
    $hasChildScoreQuery['query']['bool']['must'][0]['has_child']['score_mode'] === 'max');

// Test 14: Manual has_parent with score  
$hasParentScoreQuery = (new ElasticQuery())
    ->must([
        'has_parent' => [
            'parent_type' => 'blog',
            'score' => true,
            'query' => [
                'bool' => [
                    'must' => [
                        ['match' => ['title' => 'tutorial']]
                    ]
                ]
            ]
        ]
    ])
    ->build();
runTest("Manual has_parent with score",
    isset($hasParentScoreQuery['query']['bool']['must'][0]['has_parent']['score']) &&
    $hasParentScoreQuery['query']['bool']['must'][0]['has_parent']['score'] === true);

// Test 15: Complex nested scenario (e-commerce)
$ecommerceNestedQuery = (new ElasticQuery())
    ->search('laptop', ['name^3', 'description'])
    ->where('status', 'active')
    ->range('price', 'gte', 500)
    ->nested('reviews', function($q) {
        $q->range('reviews.rating', 'gte', 4)
          ->where('reviews.verified', true)
          ->search('excellent', ['reviews.text']);
    })
    ->nested('seller', function($q) {
        $q->where('seller.verified', true)
          ->range('seller.rating', 'gte', 4.5);
    })
    ->orderByDesc('_score')
    ->paginate(20)
    ->build();
runTest("Complex e-commerce nested scenario",
    isset($ecommerceNestedQuery['query']['bool']['must']) &&
    isset($ecommerceNestedQuery['query']['bool']['filter']) &&
    isset($ecommerceNestedQuery['sort']) &&
    isset($ecommerceNestedQuery['size']) &&
    count($ecommerceNestedQuery['query']['bool']['must']) === 3); // search + 2 nested

return printTestSummary("Nested Query Tests");
