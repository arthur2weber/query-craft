<?php
/**
 * Comprehensive QueryCraft Examples
 * 
 * This file demonstrates all the features and capabilities of the QueryCraft DSL Builder
 * with practical examples covering basic searches, advanced queries, aggregations, and more.
 * 
 * @package Arthur2weber\QueryCraft
 * @author Arthur
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Arthur2weber\QueryCraft\ElasticQuery;

echo "================================================================================\n";
echo "🌟 QUERYCRAFT - COMPREHENSIVE EXAMPLES\n";
echo "================================================================================\n\n";

echo "📝 SECTION 1: BASIC EXAMPLES\n";
echo str_repeat("-", 50) . "\n";

// ==================================================
// EXAMPLE 1: Basic search with filters
// ==================================================
echo "\n🔹 EXAMPLE 1: Basic search with filters\n";
$basicQuery = new ElasticQuery();
$query1 = $basicQuery
    ->match('title', 'PHP development')
    ->filter('status', 'published')
    ->filter('category', 'programming')
    ->range('created_at', 'gte', '2024-01-01')
    ->size(20)
    ->sort('created_at', 'desc')
    ->build();

echo "✅ Basic query created successfully!\n";
echo "Methods used: match(), filter(), range(), size(), sort()\n";
echo "JSON Output:\n" . json_encode($query1, JSON_PRETTY_PRINT) . "\n";

// ==================================================
// EXAMPLE 2: Search with boost and relevance
// ==================================================
echo "\n🔹 EXAMPLE 2: Search with boost and relevance\n";
$boostedQuery = new ElasticQuery();
$query2 = $boostedQuery
    ->matchBoost('title', 'Laravel', 3.0)
    ->matchBoost('content', 'Laravel', 1.5)
    ->should(ElasticQuery::matchClause('tags', 'framework'))
    ->termBoost('difficulty', 'intermediate', 2.0)
    ->mustNot(ElasticQuery::termClause('status', 'draft'))
    ->size(10)
    ->build();

echo "✅ Boosted query created successfully!\n";
echo "Methods used: matchBoost(), should(), termBoost(), mustNot(), static methods\n";
echo "JSON Output:\n" . json_encode($query2, JSON_PRETTY_PRINT) . "\n";

// ==================================================
// EXAMPLE 3: Complex aggregations
// ==================================================
echo "\n🔹 EXAMPLE 3: Complex aggregations\n";
$aggregationQuery = new ElasticQuery();
$query3 = $aggregationQuery
    ->match('category', 'technology')
    ->range('publish_date', 'gte', '2024-01-01')
    ->aggregation('categories', ['terms' => ['field' => 'category.keyword', 'size' => 10]])
    ->aggregation('monthly_posts', ['date_histogram' => [
        'field' => 'publish_date',
        'calendar_interval' => 'month'
    ]])
    ->aggregation('avg_views', ['avg' => ['field' => 'views']])
    ->aggregation('max_comments', ['max' => ['field' => 'comments_count']])
    ->size(0)
    ->build();

echo "✅ Complex aggregation query created successfully!\n";
echo "Methods used: aggregation() with various types (terms, date_histogram, avg, max)\n";
echo "JSON Output:\n" . json_encode($query3, JSON_PRETTY_PRINT) . "\n";

echo "\n📝 SECTION 2: ADVANCED SEARCH PATTERNS\n";
echo str_repeat("-", 50) . "\n";

// ==================================================
// EXAMPLE 4: Multi-field search
// ==================================================
echo "\n🔹 EXAMPLE 4: Multi-field search with different boosts\n";
$multiFieldQuery = new ElasticQuery();
$query4 = $multiFieldQuery
    ->search('Elasticsearch guide', ['title^3', 'content^1', 'tags^2'])
    ->filter('status', 'published')
    ->filter('type', 'article')
    ->range('rating', 'gte', 4.0)
    ->sort('_score', 'desc')
    ->sort('publish_date', 'desc')
    ->size(15)
    ->build();

echo "✅ Multi-field search created successfully!\n";
echo "Methods used: search() with field boosts\n";
echo "JSON Output:\n" . json_encode($query4, JSON_PRETTY_PRINT) . "\n";

// ==================================================
// EXAMPLE 5: Boolean combinations
// ==================================================
echo "\n🔹 EXAMPLE 5: Complex boolean combinations\n";
$booleanQuery = new ElasticQuery();
$query5 = $booleanQuery
    ->must(ElasticQuery::matchClause('title', 'Python'))
    ->should(ElasticQuery::matchClause('tags', 'tutorial'))
    ->should(ElasticQuery::matchClause('tags', 'beginner'))
    ->mustNot(ElasticQuery::termClause('status', 'archived'))
    ->mustNot(ElasticQuery::termClause('private', true))
    ->filter('language', 'en')
    ->minimumShouldMatch(1)
    ->build();

echo "✅ Boolean combination query created successfully!\n";
echo "Methods used: must(), should(), mustNot(), minimumShouldMatch()\n";
echo "JSON Output:\n" . json_encode($query5, JSON_PRETTY_PRINT) . "\n";

echo "\n📝 SECTION 3: SPECIALIZED SEARCHES\n";
echo str_repeat("-", 50) . "\n";

// ==================================================
// EXAMPLE 6: Fuzzy and wildcard search
// ==================================================
echo "\n🔹 EXAMPLE 6: Fuzzy and wildcard search\n";
$fuzzyQuery = new ElasticQuery();
$query6 = $fuzzyQuery
    ->fuzzy('title', 'javscript', 2) // Allows 2 character differences
    ->wildcard('author', 'john*')
    ->prefix('category', 'prog')
    ->range('word_count', 'gte', 500)
    ->range('word_count', 'lte', 5000)
    ->sort('relevance_score', 'desc')
    ->build();

echo "✅ Fuzzy and wildcard query created successfully!\n";
echo "Methods used: fuzzy(), wildcard(), prefix()\n";
echo "JSON Output:\n" . json_encode($query6, JSON_PRETTY_PRINT) . "\n";

// ==================================================
// EXAMPLE 7: Geo-spatial search
// ==================================================
echo "\n🔹 EXAMPLE 7: Geo-spatial search\n";
$geoQuery = new ElasticQuery();
$query7 = $geoQuery
    ->geoDistance('location', '40.7128,-74.0060', '10km')
    ->filter('category', 'restaurant')
    ->filter('open_now', true)
    ->range('rating', 'gte', 4.0)
    ->aggregation('nearby_count', ['value_count' => ['field' => 'id']])
    ->aggregation('avg_rating', ['avg' => ['field' => 'rating']])
    ->addSort([
        '_geo_distance' => [
            'location' => '40.7128,-74.0060',
            'order' => 'asc',
            'unit' => 'km'
        ]
    ])
    ->size(25)
    ->build();

echo "✅ Geo-spatial query created successfully!\n";
echo "Methods used: geoDistance(), geo sorting\n";
echo "JSON Output:\n" . json_encode($query7, JSON_PRETTY_PRINT) . "\n";

echo "\n📝 SECTION 4: ANALYTICS AND REPORTING\n";
echo str_repeat("-", 50) . "\n";

// ==================================================
// EXAMPLE 8: Advanced analytics aggregations
// ==================================================
echo "\n🔹 EXAMPLE 8: Advanced analytics aggregations\n";
$analyticsQuery = new ElasticQuery();
$query8 = $analyticsQuery
    ->range('timestamp', 'gte', '2024-01-01')
    ->range('timestamp', 'lte', '2024-12-31')
    ->aggregation('monthly_sales', ['date_histogram' => [
        'field' => 'timestamp',
        'calendar_interval' => 'month',
        'format' => 'yyyy-MM'
    ]])
    ->aggregation('sales_stats', ['stats' => ['field' => 'amount']])
    ->aggregation('top_products', ['terms' => [
        'field' => 'product_id.keyword',
        'size' => 10,
        'order' => ['total_sales' => 'desc']
    ]])
    ->aggregation('revenue_percentiles', ['percentiles' => [
        'field' => 'amount',
        'percents' => [25, 50, 75, 95, 99]
    ]])
    ->size(0) // Only aggregations, no documents
    ->build();

echo "✅ Advanced analytics query created successfully!\n";
echo "Methods used: Multiple aggregation types (date_histogram, stats, percentiles)\n";
echo "JSON Output:\n" . json_encode($query8, JSON_PRETTY_PRINT) . "\n";

// ==================================================
// EXAMPLE 9: Nested aggregations
// ==================================================
echo "\n🔹 EXAMPLE 9: Nested aggregations with sub-aggregations\n";
$nestedAggQuery = new ElasticQuery();
$query9 = $nestedAggQuery
    ->filter('status', 'active')
    ->aggregation('categories', ['terms' => [
        'field' => 'category.keyword',
        'size' => 5,
        'aggs' => [
            'monthly_trend' => [
                'date_histogram' => [
                    'field' => 'created_at',
                    'calendar_interval' => 'month'
                ]
            ],
            'avg_score' => [
                'avg' => [
                    'field' => 'score'
                ]
            ]
        ]
    ]])
    ->size(0)
    ->build();

echo "✅ Nested aggregations query created successfully!\n";
echo "Methods used: aggregation() with nested sub-aggregations\n";
echo "JSON Output:\n" . json_encode($query9, JSON_PRETTY_PRINT) . "\n";

echo "\n📝 SECTION 5: PERFORMANCE OPTIMIZATIONS\n";
echo str_repeat("-", 50) . "\n";

// ==================================================
// EXAMPLE 10: Performance-optimized query
// ==================================================
echo "\n🔹 EXAMPLE 10: Performance-optimized query\n";
$optimizedQuery = new ElasticQuery();
$query10 = $optimizedQuery
    ->filter('status', 'published') // Filters first for performance
    ->filter('category', 'technology')
    ->range('created_at', 'gte', 'now-30d')
    ->match('content', 'machine learning')
    ->source(['id', 'title', 'summary', 'created_at']) // Limited source fields
    ->size(50)
    ->timeout('5s')
    ->sort('created_at', 'desc')
    ->build();

echo "✅ Performance-optimized query created successfully!\n";
echo "Methods used: Filters before queries, source(), timeout()\n";
echo "JSON Output:\n" . json_encode($query10, JSON_PRETTY_PRINT) . "\n";

echo "\n================================================================================\n";
echo "🎉 ALL EXAMPLES COMPLETED SUCCESSFULLY!\n";
echo "================================================================================\n";
echo "Total examples demonstrated: 10\n";
echo "Features covered:\n";
echo "  ✓ Basic search and filtering\n";
echo "  ✓ Boosting and relevance scoring\n";
echo "  ✓ Complex aggregations\n";
echo "  ✓ Multi-field searches\n";
echo "  ✓ Boolean query combinations\n";
echo "  ✓ Fuzzy and wildcard matching\n";
echo "  ✓ Geo-spatial searches\n";
echo "  ✓ Advanced analytics\n";
echo "  ✓ Nested aggregations\n";
echo "  ✓ Performance optimizations\n";
echo "\nFor more specific use cases, check out the specialized example files:\n";
echo "  📄 blog-search.php - Blog content search examples\n";
echo "  📄 ecommerce-search.php - E-commerce product search\n";
echo "  📄 geographic-search.php - Location-based search\n";
echo "  📄 analytics-aggregations.php - Analytics and reporting\n";
echo "================================================================================\n";
