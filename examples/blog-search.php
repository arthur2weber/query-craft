<?php
/**
 * Blog Search Example
 * 
 * This example demonstrates how to build a complete blog search system
 * with full-text search, category filters, date ranges, and aggregations.
 * 
 * Features demonstrated:
 * - Multi-field text search with boosting
 * - Status and category filtering
 * - Date range queries
 * - Aggregations for faceted search
 * - Pagination
 * 
 * @package Arthur2weber\QueryCraft\Examples
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Arthur2weber\QueryCraft\ElasticQuery;

echo "🔍 Blog Search Example\n";
echo str_repeat("=", 50) . "\n\n";

// Basic blog post search
echo "1. Basic blog search with text and filters:\n";
$blogQuery = new ElasticQuery();
$basicBlogSearch = $blogQuery
    ->search('Laravel Eloquent tutorial', ['title^3', 'content^1', 'tags^2'])
    ->where('status', 'published')
    ->where('published_at', '<=', date('Y-m-d H:i:s'))
    ->whereIn('category', ['programming', 'web-development', 'php'])
    ->recent(90)
    ->latest('published_at')
    ->paginate(12, 1);

echo "✅ Basic blog search query built successfully\n";
echo "DSL Preview:\n" . $basicBlogSearch->toDSL() . "\n\n";

// Advanced blog search with aggregations
echo "2. Advanced blog search with category facets:\n";
$advancedBlogQuery = new ElasticQuery();
$advancedBlogSearch = $advancedBlogQuery
    ->search('PHP Laravel framework', ['title^3', 'content', 'excerpt^2'])
    ->searchPhrase('content', 'step by step')
    ->where('status', 'published')
    ->where('reading_time', '<=', 15)
    ->whereNotNull('featured_image')
    ->should(ElasticQuery::termBoostClause('featured', true, 2.0))
    ->aggregation('categories', [
        'terms' => ['field' => 'category.keyword', 'size' => 10]
    ])
    ->aggregation('authors', [
        'terms' => ['field' => 'author.keyword', 'size' => 5]
    ])
    ->aggregation('avg_reading_time', [
        'avg' => ['field' => 'reading_time']
    ])
    ->aggregation('monthly_posts', [
        'date_histogram' => [
            'field' => 'published_at',
            'calendar_interval' => 'month',
            'format' => 'yyyy-MM'
        ]
    ])
    ->orderByDesc('_score')
    ->orderByDesc('published_at')
    ->paginate(15);

echo "✅ Advanced blog search with aggregations built successfully\n";
echo "Aggregations: categories, authors, avg_reading_time, monthly_posts\n\n";

// Blog search with conditional filters
echo "3. Conditional blog search (user role-based):\n";
$userRole = 'subscriber'; // Could be 'admin', 'editor', 'subscriber'
$showDrafts = false;

$conditionalBlogQuery = new ElasticQuery();
$conditionalBlogSearch = $conditionalBlogQuery
    ->search('JavaScript React Vue', ['title^2', 'content'])
    ->when($userRole === 'admin', function($q) {
        return $q->whereIn('status', ['published', 'draft', 'pending']);
    })
    ->when($userRole === 'editor', function($q) {
        return $q->whereIn('status', ['published', 'pending']);
    })
    ->unless($userRole === 'admin' || $userRole === 'editor', function($q) {
        return $q->where('status', 'published');
    })
    ->when($showDrafts && $userRole === 'admin', function($q) {
        return $q->should(ElasticQuery::termClause('status', 'draft'));
    })
    ->active()
    ->recent(180)
    ->orderByDesc('views')
    ->limit(20);

echo "✅ Conditional blog search built successfully\n";
echo "Applied role-based filters for user: {$userRole}\n\n";

echo "🎯 Blog Search Features Demonstrated:\n";
echo "• Multi-field text search with field boosting\n";
echo "• Phrase matching for exact content\n";
echo "• Status and category filtering\n";
echo "• Date range queries (recent posts)\n";
echo "• Aggregations for faceted navigation\n";
echo "• Conditional logic based on user roles\n";
echo "• Pagination and sorting\n";
echo "• Featured content boosting\n\n";

echo "💡 Usage Tips:\n";
echo "• Use title^3 to boost title matches 3x\n";
echo "• Combine search() with searchPhrase() for precise matching\n";
echo "• Use recent() for time-based filtering\n";
echo "• Add aggregations for category/author facets\n";
echo "• Apply conditional logic with when()/unless()\n";
