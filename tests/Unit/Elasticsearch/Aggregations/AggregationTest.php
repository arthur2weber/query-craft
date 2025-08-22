<?php
/**
 * Aggregation Tests
 * 
 * Tests aggregation functionality:
 * - aggregation() method
 * - subAggregation() method
 * - Eloquent-style aggregation methods (count, avg, sum, min, max)
 * - Complex nested aggregations
 * 
 * @package Arthur2weber\QueryCraft\Tests\Unit\Aggregations
 */

require_once __DIR__ . '/../../../../src/ElasticQueryInterface.php';
require_once __DIR__ . '/../../../../src/ElasticQuery.php';

use Arthur2weber\QueryCraft\ElasticQuery;

echo "🧪 AGGREGATIONS: Aggregation Tests\n";
echo str_repeat("=", 40) . "\n\n";

$successCount = 0;
$totalTests = 0;

function runTest($description, $result) {
    global $successCount, $totalTests;
    $totalTests++;
    echo ($result ? "✅" : "❌") . " $description\n";
    if ($result) $successCount++;
}

// Test 1: Basic aggregation
$basicAggQuery = (new ElasticQuery())
    ->aggregation('avg_price', ['avg' => ['field' => 'price']])
    ->build();
runTest("Basic aggregation",
    isset($basicAggQuery['aggs']['avg_price']['avg']['field']) &&
    $basicAggQuery['aggs']['avg_price']['avg']['field'] === 'price');

// Test 2: Terms aggregation
$termsAggQuery = (new ElasticQuery())
    ->aggregation('categories', ['terms' => ['field' => 'category.keyword', 'size' => 10]])
    ->build();
runTest("Terms aggregation",
    isset($termsAggQuery['aggs']['categories']['terms']['field']) &&
    isset($termsAggQuery['aggs']['categories']['terms']['size']) &&
    $termsAggQuery['aggs']['categories']['terms']['size'] === 10);

// Test 3: Date histogram aggregation
$dateHistAggQuery = (new ElasticQuery())
    ->aggregation('monthly_sales', [
        'date_histogram' => [
            'field' => 'created_at',
            'calendar_interval' => 'month'
        ]
    ])
    ->build();
runTest("Date histogram aggregation",
    isset($dateHistAggQuery['aggs']['monthly_sales']['date_histogram']['field']) &&
    $dateHistAggQuery['aggs']['monthly_sales']['date_histogram']['calendar_interval'] === 'month');

// Test 4: Range aggregation
$rangeAggQuery = (new ElasticQuery())
    ->aggregation('price_ranges', [
        'range' => [
            'field' => 'price',
            'ranges' => [
                ['to' => 100],
                ['from' => 100, 'to' => 500],
                ['from' => 500]
            ]
        ]
    ])
    ->build();
runTest("Range aggregation",
    isset($rangeAggQuery['aggs']['price_ranges']['range']['field']) &&
    count($rangeAggQuery['aggs']['price_ranges']['range']['ranges']) === 3);

// Test 5: Multiple aggregations
$multiAggQuery = (new ElasticQuery())
    ->aggregation('avg_price', ['avg' => ['field' => 'price']])
    ->aggregation('max_rating', ['max' => ['field' => 'rating']])
    ->aggregation('categories', ['terms' => ['field' => 'category.keyword']])
    ->build();
runTest("Multiple aggregations",
    isset($multiAggQuery['aggs']['avg_price']) &&
    isset($multiAggQuery['aggs']['max_rating']) &&
    isset($multiAggQuery['aggs']['categories']) &&
    count($multiAggQuery['aggs']) === 3);

// Test 6: Manual sub-aggregation (nested aggs structure)
$subAggQuery = (new ElasticQuery())
    ->aggregation('categories', [
        'terms' => ['field' => 'category.keyword'],
        'aggs' => [
            'avg_price' => ['avg' => ['field' => 'price']]
        ]
    ])
    ->build();
runTest("Manual sub-aggregation structure",
    isset($subAggQuery['aggs']['categories']['aggs']['avg_price']['avg']['field']) &&
    $subAggQuery['aggs']['categories']['aggs']['avg_price']['avg']['field'] === 'price');

// Test 7: count() Eloquent method
$countQuery = (new ElasticQuery())
    ->where('status', 'active')
    ->count()
    ->build();
runTest("count() Eloquent method",
    isset($countQuery['aggs']['total_count']['value_count']['field']) &&
    isset($countQuery['size']) &&
    $countQuery['size'] === 0);

// Test 8: avg() Eloquent method
$avgQuery = (new ElasticQuery())
    ->avg('rating')
    ->build();
runTest("avg() Eloquent method",
    isset($avgQuery['aggs']['avg_rating']['avg']['field']) &&
    $avgQuery['aggs']['avg_rating']['avg']['field'] === 'rating');

// Test 9: sum() Eloquent method
$sumQuery = (new ElasticQuery())
    ->sum('total_amount')
    ->build();
runTest("sum() Eloquent method",
    isset($sumQuery['aggs']['sum_total_amount']['sum']['field']) &&
    $sumQuery['aggs']['sum_total_amount']['sum']['field'] === 'total_amount');

// Test 10: max() Eloquent method
$maxQuery = (new ElasticQuery())
    ->max('score')
    ->build();
runTest("max() Eloquent method",
    isset($maxQuery['aggs']['max_score']['max']['field']) &&
    $maxQuery['aggs']['max_score']['max']['field'] === 'score');

// Test 11: min() Eloquent method
$minQuery = (new ElasticQuery())
    ->min('price')
    ->build();
runTest("min() Eloquent method",
    isset($minQuery['aggs']['min_price']['min']['field']) &&
    $minQuery['aggs']['min_price']['min']['field'] === 'price');

// Test 12: Complex nested aggregation (manual structure)
$nestedAggQuery = (new ElasticQuery())
    ->aggregation('categories', [
        'terms' => ['field' => 'category.keyword'],
        'aggs' => [
            'monthly_trend' => [
                'date_histogram' => [
                    'field' => 'created_at',
                    'calendar_interval' => 'month'
                ]
            ],
            'avg_score' => ['avg' => ['field' => 'score']]
        ]
    ])
    ->build();
runTest("Complex nested aggregation structure",
    isset($nestedAggQuery['aggs']['categories']['aggs']['monthly_trend']) &&
    isset($nestedAggQuery['aggs']['categories']['aggs']['avg_score']));

// Test 13: Aggregation with query filters
$aggWithFilterQuery = (new ElasticQuery())
    ->where('status', 'published')
    ->range('created_at', 'gte', '2024-01-01')
    ->aggregation('avg_views', ['avg' => ['field' => 'views']])
    ->aggregation('top_authors', ['terms' => ['field' => 'author.keyword', 'size' => 5]])
    ->build();
runTest("Aggregation with query filters",
    isset($aggWithFilterQuery['query']['bool']['filter']) &&
    isset($aggWithFilterQuery['aggs']['avg_views']) &&
    isset($aggWithFilterQuery['aggs']['top_authors']));

// Test 14: Empty aggregation name validation
try {
    (new ElasticQuery())->aggregation('', ['avg' => ['field' => 'price']]);
    runTest("Empty aggregation name validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Empty aggregation name validation", true);
}

// Test 15: Empty aggregation config validation
try {
    (new ElasticQuery())->aggregation('test', []);
    runTest("Empty aggregation config validation", false);
} catch (\InvalidArgumentException $e) {
    runTest("Empty aggregation config validation", true);
}

// Test 16: Multiple Eloquent aggregations
$multiEloquentAggQuery = (new ElasticQuery())
    ->where('category', 'electronics')
    ->count()
    ->avg('price')
    ->max('rating')
    ->sum('sales_count')
    ->build();
runTest("Multiple Eloquent aggregations",
    isset($multiEloquentAggQuery['aggs']['total_count']) &&
    isset($multiEloquentAggQuery['aggs']['avg_price']) &&
    isset($multiEloquentAggQuery['aggs']['max_rating']) &&
    isset($multiEloquentAggQuery['aggs']['sum_sales_count']) &&
    count($multiEloquentAggQuery['aggs']) === 4);

echo "\n" . str_repeat("=", 40) . "\n";
echo "📊 Aggregation Tests: $successCount/$totalTests passed\n";
echo "📈 Success Rate: " . round(($successCount/$totalTests) * 100, 1) . "%\n";

return ['passed' => $successCount, 'total' => $totalTests];
