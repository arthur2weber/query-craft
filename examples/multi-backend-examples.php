<?php

/**
 * QueryCraft Multi-Backend Examples
 * 
 * This file demonstrates how to use QueryCraft with different backends
 * using the same familiar Eloquent-like syntax.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Arthur2weber\QueryCraft\QueryCraft;

echo "=== QueryCraft Multi-Backend Examples ===\n\n";

// =============================================================================
// Example 1: Same Query Syntax, Different Backends
// =============================================================================

echo "1. SAME SYNTAX, DIFFERENT BACKENDS\n";
echo "-----------------------------------\n";

// The same query logic for all backends
$basicQuery = function($queryBuilder) {
    return $queryBuilder
        ->from('users')
        ->where('status', 'active')
        ->where('age', '>=', 18)
        ->whereBetween('created_at', ['2024-01-01', '2024-12-31'])
        ->orderBy('name', 'asc')
        ->take(10);
};

// Elasticsearch
echo "Elasticsearch Query:\n";
$elasticQuery = $basicQuery(QueryCraft::elastic())->toQuery();
echo json_encode($elasticQuery, JSON_PRETTY_PRINT) . "\n\n";

// MongoDB
echo "MongoDB Query:\n";
$mongoQuery = $basicQuery(QueryCraft::mongo())->toQuery();
echo json_encode($mongoQuery, JSON_PRETTY_PRINT) . "\n\n";

// GraphQL
echo "GraphQL Query:\n";
$graphQuery = $basicQuery(QueryCraft::graph())->toQuery();
echo $graphQuery . "\n\n";

// =============================================================================
// Example 2: E-commerce Product Search Across Backends
// =============================================================================

echo "2. E-COMMERCE PRODUCT SEARCH\n";
echo "-----------------------------\n";

// Elasticsearch - Full-text search with aggregations
echo "Elasticsearch - Rich search with aggregations:\n";
$productSearch = QueryCraft::elastic()
    ->from('products')
    ->search('wireless headphones', ['name^3', 'description', 'brand^2'])
    ->where('status', 'active')
    ->where('in_stock', true)
    ->whereBetween('price', [50, 300])
    ->whereIn('brand', ['sony', 'bose', 'sennheiser'])
    ->highlight(['name', 'description'])
    ->aggregation('brands', ['terms' => ['field' => 'brand.keyword']])
    ->aggregation('price_ranges', [
        'range' => [
            'field' => 'price',
            'ranges' => [
                ['to' => 100],
                ['from' => 100, 'to' => 200],
                ['from' => 200]
            ]
        ]
    ])
    ->orderBy('_score', 'desc')
    ->take(20)
    ->toQuery();

echo json_encode($productSearch, JSON_PRETTY_PRINT) . "\n\n";

// MongoDB - Aggregation pipeline with lookups
echo "MongoDB - Complex aggregation with user data:\n";
$orderAnalysis = QueryCraft::mongo()
    ->from('orders')
    ->where('status', 'completed')
    ->whereBetween('created_at', ['2024-01-01', '2024-12-31'])
    ->lookup('users', 'user_id', '_id', 'user')
    ->unwind('$user')
    ->lookup('products', 'items.product_id', '_id', 'product_details')
    ->group([
        '_id' => '$user.country',
        'total_sales' => ['$sum' => '$total_amount'],
        'order_count' => ['$sum' => 1],
        'avg_order_value' => ['$avg' => '$total_amount'],
        'top_products' => ['$push' => '$product_details.name']
    ])
    ->orderBy('total_sales', 'desc')
    ->take(10)
    ->toQuery();

echo json_encode($orderAnalysis, JSON_PRETTY_PRINT) . "\n\n";

// GraphQL - Complex query with nested relations
echo "GraphQL - Comprehensive data fetching:\n";
$userProfile = QueryCraft::graph()
    ->operation('query', 'GetUserProfile')
    ->variable('userId', 'ID!', null)
    ->variable('includeOrders', 'Boolean', false)
    ->from('user')
    ->where('id', '$userId')
    ->select([
        'id',
        'name',
        'email',
        'profile { bio, avatar, preferences }',
        'addresses { street, city, country, isDefault }'
    ])
    ->with([
        'orders' => [
            'id', 'status', 'total', 'createdAt',
            'items { quantity, price, product { name, image } }'
        ],
        'reviews' => [
            'id', 'rating', 'comment', 'createdAt',
            'product { name, category }'
        ]
    ])
    ->directive('include', ['if' => '$includeOrders'])
    ->toQuery();

echo $userProfile . "\n\n";

// =============================================================================
// Example 3: Analytics Dashboard Queries
// =============================================================================

echo "3. ANALYTICS DASHBOARD QUERIES\n";
echo "-------------------------------\n";

// Real-time metrics with Elasticsearch
echo "Elasticsearch - Real-time website metrics:\n";
$realtimeMetrics = QueryCraft::elastic()
    ->from('page_views')
    ->where('timestamp', '>=', 'now-1h')
    ->aggregation('page_views_over_time', [
        'date_histogram' => [
            'field' => 'timestamp',
            'interval' => '5m'
        ]
    ])
    ->aggregation('top_pages', [
        'terms' => [
            'field' => 'page.keyword',
            'size' => 10
        ]
    ])
    ->aggregation('avg_load_time', [
        'avg' => ['field' => 'load_time']
    ])
    ->size(0) // We only want aggregations
    ->toQuery();

echo json_encode($realtimeMetrics, JSON_PRETTY_PRINT) . "\n\n";

// User behavior analysis with MongoDB
echo "MongoDB - User behavior analysis:\n";
$userBehavior = QueryCraft::mongo()
    ->from('user_sessions')
    ->whereBetween('start_time', ['2024-08-01', '2024-08-31'])
    ->group([
        '_id' => [
            'day' => ['$dayOfMonth' => '$start_time'],
            'hour' => ['$hour' => '$start_time']
        ],
        'session_count' => ['$sum' => 1],
        'avg_duration' => ['$avg' => '$duration'],
        'unique_users' => ['$addToSet' => '$user_id'],
        'bounce_rate' => [
            '$avg' => [
                '$cond' => [
                    ['$lte' => ['$page_views', 1]],
                    1,
                    0
                ]
            ]
        ]
    ])
    ->project([
        'day' => '$_id.day',
        'hour' => '$_id.hour',
        'session_count' => 1,
        'avg_duration' => 1,
        'unique_users_count' => ['$size' => '$unique_users'],
        'bounce_rate' => ['$multiply' => ['$bounce_rate', 100]]
    ])
    ->orderBy('day', 'asc')
    ->orderBy('hour', 'asc')
    ->toQuery();

echo json_encode($userBehavior, JSON_PRETTY_PRINT) . "\n\n";

// =============================================================================
// Example 4: Migration Between Backends
// =============================================================================

echo "4. EASY MIGRATION BETWEEN BACKENDS\n";
echo "-----------------------------------\n";

// Define a query that works across all backends
$migrationQuery = function($backend) {
    return QueryCraft::for($backend)
        ->from('articles')
        ->where('published', true)
        ->where('category', 'technology')
        ->whereBetween('publish_date', ['2024-01-01', '2024-12-31'])
        ->orderBy('publish_date', 'desc')
        ->take(50);
};

echo "The same query logic across different backends:\n\n";

foreach (['elasticsearch', 'mongodb', 'graphql'] as $backend) {
    echo "Backend: " . strtoupper($backend) . "\n";
    try {
        $query = $migrationQuery($backend)->toQuery();
        if (is_array($query)) {
            echo json_encode($query, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo $query . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// =============================================================================
// Example 5: Backend-Specific Features
// =============================================================================

echo "5. BACKEND-SPECIFIC FEATURES\n";
echo "-----------------------------\n";

// Elasticsearch specific features
echo "Elasticsearch - Advanced search features:\n";
$advancedSearch = QueryCraft::elastic()
    ->from('documents')
    ->search('machine learning', ['title^3', 'content', 'tags^2'])
    ->must(['range' => ['relevance_score' => ['gte' => 0.8]]])
    ->should(['term' => ['featured' => true]], 1)
    ->mustNot(['term' => ['status' => 'archived']])
    ->highlight(['title', 'content'], '<mark>', '</mark>')
    ->minimumShouldMatch('75%')
    ->fuzzy('title', 'machne learing', ['fuzziness' => 'AUTO'])
    ->aggregation('categories', ['terms' => ['field' => 'category.keyword']])
    ->toQuery();

echo json_encode($advancedSearch, JSON_PRETTY_PRINT) . "\n\n";

// MongoDB specific features
echo "MongoDB - Geospatial and text search:\n";
$locationSearch = QueryCraft::mongo()
    ->from('restaurants')
    ->where('cuisine', 'italian')
    ->where('rating', '>=', 4.0)
    ->near('location', [
        'type' => 'Point',
        'coordinates' => [-74.0060, 40.7128] // NYC coordinates
    ], ['maxDistance' => 5000]) // 5km radius
    ->textSearch('pizza pasta', ['language' => 'en'])
    ->project([
        'name' => 1,
        'cuisine' => 1,
        'rating' => 1,
        'address' => 1,
        'distance' => ['$meta' => 'geoNearDistance']
    ])
    ->orderBy('distance', 'asc')
    ->take(20)
    ->toQuery();

echo json_encode($locationSearch, JSON_PRETTY_PRINT) . "\n\n";

// GraphQL specific features
echo "GraphQL - Complex mutations and subscriptions:\n";
$complexGraphQL = QueryCraft::graph()
    ->operation('mutation', 'CreatePostWithTags')
    ->variable('input', 'CreatePostInput!', null)
    ->variable('tagIds', '[ID!]!', null)
    ->from('createPost')
    ->select([
        'id',
        'title',
        'content',
        'author { id, name, avatar }',
        'tags { id, name, color }',
        'createdAt',
        'updatedAt'
    ])
    ->fragment('PostFragment', 'Post', [
        'id',
        'title',
        'slug',
        'excerpt',
        'publishedAt'
    ])
    ->directive('auth', ['requires' => 'AUTHOR'])
    ->toQuery();

echo $complexGraphQL . "\n\n";

echo "=== End of Examples ===\n\n";

echo "🎉 QueryCraft now supports TRUE multi-backend queries!\n";
echo "✨ Same syntax, different backends, infinite possibilities!\n";
