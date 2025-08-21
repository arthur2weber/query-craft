<?php
/**
 * Laravel Migration Examples - QueryCraft vs Eloquent
 * 
 * This file demonstrates how to migrate from Laravel Eloquent to QueryCraft
 * with zero learning curve. All your familiar methods work exactly the same!
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Arthur2weber\QueryCraft\ElasticQuery;

echo "🚀 Laravel Migration Examples - QueryCraft vs Eloquent\n";
echo str_repeat("=", 60) . "\n\n";

// ==================================================
// EXAMPLE 1: Basic Eloquent to QueryCraft Migration
// ==================================================
echo "📝 EXAMPLE 1: Basic Where Clauses Migration\n";
echo str_repeat("-", 40) . "\n";

echo "Before (Eloquent):\n";
echo "\$posts = Post::where('status', 'published')\n";
echo "             ->whereIn('category', ['tech', 'programming'])\n";
echo "             ->orderByDesc('created_at')\n";
echo "             ->paginate(15);\n\n";

echo "After (QueryCraft) - Identical syntax:\n";
$basicMigration = new ElasticQuery();
$basicQuery = $basicMigration
    ->where('status', 'published')
    ->whereIn('category', ['tech', 'programming'])
    ->orderByDesc('created_at')
    ->paginate(15)
    ->build();

echo "✅ Zero learning curve migration completed!\n";
echo "Methods used: where(), whereIn(), orderByDesc(), paginate()\n\n";

// ==================================================
// EXAMPLE 2: Enhanced with Elasticsearch Features
// ==================================================
echo "📝 EXAMPLE 2: Enhanced with Full-Text Search\n";
echo str_repeat("-", 40) . "\n";

echo "Enhanced with Elasticsearch superpowers:\n";
$enhancedMigration = new ElasticQuery();
$enhancedQuery = $enhancedMigration
    ->search('Laravel tutorial', ['title^3', 'content'])  // 🆕 Full-text search
    ->where('status', 'published')                        // Same Eloquent syntax
    ->whereIn('category', ['tech', 'programming'])        // Same Eloquent syntax
    ->orderByDesc('_score')                               // 🆕 Sort by relevance
    ->orderByDesc('created_at')                           // Same Eloquent syntax
    ->paginate(15)                                        // Same Eloquent syntax
    ->build();

echo "✅ Enhanced with full-text search and relevance scoring!\n";
echo "New features: search(), _score sorting, relevance ranking\n\n";

// ==================================================
// EXAMPLE 3: Complex Laravel Controller Migration
// ==================================================
echo "📝 EXAMPLE 3: Laravel Controller Migration\n";
echo str_repeat("-", 40) . "\n";

echo "Before (Laravel Controller with Eloquent):\n";
echo "// PostController@index\n";
echo "// \$posts = Post::query()\n";
echo "//     ->when(\$request->search, function(\$q) use (\$request) {\n";
echo "//         return \$q->where('title', 'like', '%' . \$request->search . '%')\n";
echo "//                  ->orWhere('content', 'like', '%' . \$request->search . '%');\n";
echo "//     })\n";
echo "//     ->when(\$request->category, function(\$q) use (\$request) {\n";
echo "//         return \$q->where('category', \$request->category);\n";
echo "//     })\n";
echo "//     ->where('status', 'published')\n";
echo "//     ->orderByDesc('created_at')\n";
echo "//     ->paginate(15);\n\n";

echo "After (Same Controller with QueryCraft):\n";
// Simulating request parameters
$request = (object) [
    'search' => 'Laravel PHP framework',
    'category' => 'programming',
    'featured' => true
];

$controllerMigration = new ElasticQuery();
$controllerQuery = $controllerMigration
    // 🆕 Better search with relevance scoring
    ->when($request->search, function($q) use ($request) {
        return $q->search($request->search, ['title^3', 'content', 'tags^2']);
    })
    ->when($request->category, function($q) use ($request) {
        return $q->where('category', $request->category);
    })
    ->when(isset($request->featured), function($q) {
        return $q->where('featured', true);
    })
    ->where('status', 'published')
    
    // 🆕 Add faceted search for better UX
    ->aggregation('categories', [
        'terms' => ['field' => 'category.keyword', 'size' => 10]
    ])
    ->aggregation('tags', [
        'terms' => ['field' => 'tags.keyword', 'size' => 15]
    ])
    
    // 🆕 Better sorting with relevance
    ->when($request->search, function($q) {
        return $q->orderByDesc('_score');
    })
    ->orderByDesc('created_at')
    ->paginate(15)
    ->build();

echo "✅ Controller migration with enhanced features!\n";
echo "Same conditional logic, plus: faceted search, relevance scoring\n\n";

// ==================================================
// EXAMPLE 4: All Eloquent Methods Supported
// ==================================================
echo "📝 EXAMPLE 4: Complete Eloquent Method Support\n";
echo str_repeat("-", 40) . "\n";

$comprehensiveMigration = new ElasticQuery();
$comprehensiveQuery = $comprehensiveMigration
    // Where clauses (100% compatible)
    ->where('status', 'published')
    ->where('rating', '>=', 4.0)
    ->whereIn('tags', ['laravel', 'php', 'elasticsearch'])
    ->whereNotIn('status', ['draft', 'deleted'])
    ->whereBetween('created_at', ['2024-01-01', '2024-12-31'])
    ->whereNotNull('featured_image')
    ->whereNull('deleted_at')
    
    // Sorting (100% compatible)
    ->orderBy('created_at', 'desc')
    ->orderByDesc('views')
    ->latest('published_at')
    
    // Pagination (100% compatible)
    ->limit(20)
    ->offset(40)
    ->take(15)
    ->skip(30)
    
    // Aggregations (100% compatible)
    ->count()
    ->avg('rating')
    ->sum('view_count')
    ->max('price')
    ->min('created_at')
    
    // Scopes (100% compatible)
    ->active()
    ->published()
    ->recent(30)
    
    ->build();

echo "✅ All Eloquent methods work identically!\n";
echo "Methods: where, whereIn, orderBy, paginate, count, avg, scopes\n\n";

// ==================================================
// EXAMPLE 5: Advanced Elasticsearch Features
// ==================================================
echo "📝 EXAMPLE 5: Elasticsearch Superpowers Added\n";
echo str_repeat("-", 40) . "\n";

$advancedMigration = new ElasticQuery();
$advancedQuery = $advancedMigration
    // All your familiar Eloquent syntax
    ->where('status', 'active')
    ->whereIn('category', ['tech', 'science'])
    ->whereBetween('price', [100, 1000])
    ->orderByDesc('created_at')
    
    // Plus Elasticsearch superpowers
    ->search('artificial intelligence machine learning', [
        'title^4',           // Title most important
        'abstract^3',        // Abstract very important
        'content^1',         // Content normal weight
        'keywords^2'         // Keywords important
    ])
    
    // Geographic search
    ->geoDistance('author_location', '40.7128,-74.0060', '50km')
    
    // Fuzzy search (typo tolerance)
    ->fuzzy('author_name', 'jhon', 1)  // Finds "john" with 1 char difference
    
    // Advanced aggregations
    ->aggregation('top_categories', [
        'terms' => ['field' => 'category.keyword', 'size' => 10],
        'aggs' => [
            'avg_rating' => ['avg' => ['field' => 'rating']],
            'total_views' => ['sum' => ['field' => 'view_count']]
        ]
    ])
    ->aggregation('monthly_trends', [
        'date_histogram' => [
            'field' => 'created_at',
            'calendar_interval' => 'month'
        ]
    ])
    
    // Performance optimizations
    ->source(['id', 'title', 'summary', 'author', 'created_at'])
    ->timeout('5s')
    
    ->build();

echo "✅ Elasticsearch superpowers added to familiar syntax!\n";
echo "New features: Full-text search, geo queries, fuzzy search, aggregations\n\n";

// ==================================================
// EXAMPLE 6: Real E-commerce Migration
// ==================================================
echo "📝 EXAMPLE 6: E-commerce Product Search Migration\n";
echo str_repeat("-", 40) . "\n";

echo "E-commerce product search with Eloquent patterns:\n";
$ecommerceMigration = new ElasticQuery();
$ecommerceQuery = $ecommerceMigration
    // Product filtering with familiar syntax
    ->search('wireless bluetooth headphones', [
        'name^5',           // Product name most important
        'brand^3',          // Brand quite important
        'description^1',    // Description normal weight
        'features^2'        // Features moderately important
    ])
    ->where('status', 'active')
    ->where('in_stock', true)
    ->whereBetween('price', [50, 300])
    ->where('rating', '>=', 4.0)
    ->whereIn('brand', ['sony', 'bose', 'sennheiser', 'audio-technica'])
    ->whereNotIn('condition', ['refurbished', 'damaged'])
    
    // E-commerce specific aggregations
    ->aggregation('brands', [
        'terms' => ['field' => 'brand.keyword', 'size' => 20]
    ])
    ->aggregation('price_ranges', [
        'range' => [
            'field' => 'price',
            'ranges' => [
                ['key' => 'budget', 'to' => 100],
                ['key' => 'mid_range', 'from' => 100, 'to' => 200],
                ['key' => 'premium', 'from' => 200]
            ]
        ]
    ])
    ->aggregation('features', [
        'terms' => ['field' => 'features.keyword', 'size' => 15]
    ])
    
    // Smart sorting: relevance first, then rating, then price
    ->orderByDesc('_score')
    ->orderByDesc('rating')
    ->orderBy('price', 'asc')
    
    // Pagination for product listing
    ->paginate(24, 1)  // 24 products per page, page 1
    
    ->build();

echo "✅ E-commerce search with familiar Eloquent patterns!\n";
echo "Features: Product search, filtering, facets, smart sorting\n\n";

// ==================================================
// EXAMPLE 7: NEW - Laravel Integration Example
// ==================================================
echo "📝 EXAMPLE 7: Laravel Controller Dependency Injection\n";
echo str_repeat("-", 40) . "\n";

echo "QueryCraft integrates seamlessly with Laravel's dependency injection:\n";
// Example showing how QueryCraft works with Laravel DI
echo "// In your Laravel Controller:\n";
echo "class SearchController extends Controller\n";
echo "{\n";
echo "    public function search(Request \$request, ElasticQuery \$query)\n";
echo "    {\n";
echo "        return \$query\n";
echo "            ->search(\$request->get('q'))\n";
echo "            ->when(\$request->filled('category'), function(\$q) use (\$request) {\n";
echo "                return \$q->where('category', \$request->category);\n";
echo "            })\n";
echo "            ->when(\$request->get('featured'), function(\$q) {\n";
echo "                return \$q->where('featured', true);\n";
echo "            })\n";
echo "            ->published()\n";
echo "            ->recent(30)\n";
echo "            ->paginate(\$request->get('per_page', 15))\n";
echo "            ->build();\n";
echo "    }\n";
echo "}\n\n";

echo "✅ Laravel dependency injection works perfectly!\n";
echo "Features: Auto-wiring, familiar patterns, conditional queries\n\n";

// ==================================================
// Migration Summary
// ==================================================
echo "🎉 MIGRATION SUMMARY\n";
echo str_repeat("=", 30) . "\n";
echo "✅ Zero learning curve - All Eloquent methods work identically\n";
echo "✅ Enhanced power - Add Elasticsearch features incrementally\n";
echo "✅ Same patterns - Conditional queries, scopes, pagination\n";
echo "✅ Better search - Full-text search with relevance scoring\n";
echo "✅ More insights - Faceted search and aggregations\n";
echo "✅ Geographic features - Location-based search and filtering\n";
echo "✅ Performance - Optimized for large datasets\n";
echo "✅ Laravel integration - Works with DI, facades, and service containers\n\n";

echo "🚀 Start using QueryCraft today!\n";
echo "Your Eloquent skills are your QueryCraft skills!\n";
echo "💡 Tip: Begin with familiar Eloquent syntax, then add Elasticsearch superpowers!\n";
