<?php
/**
 * Analytics and Aggregations Example
 * 
 * This example demonstrates how to build complex analytics queries
 * using Elasticsearch's powerful aggregation capabilities.
 * 
 * Features demonstrated:
 * - Date histogram aggregations
 * - Terms aggregations for faceted analytics
 * - Statistical aggregations (avg, sum, min, max)
 * - Nested aggregations
 * - Pipeline aggregations
 * - Time-series analysis
 * 
 * @package Arthur2weber\QueryCraft\Examples
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Arthur2weber\QueryCraft\Query\ElasticQuery;

echo "📊 Analytics and Aggregations Example\n";
echo str_repeat("=", 50) . "\n\n";

// Website analytics dashboard
echo "1. Website analytics with daily breakdown:\n";
$analyticsQuery = new ElasticQuery();
$websiteAnalytics = $analyticsQuery
    ->whereBetween('timestamp', ['2024-01-01', '2024-12-31'])
    ->where('event_type', 'page_view')
    ->whereIn('device_type', ['desktop', 'mobile', 'tablet'])
    ->whereNotNull('user_id')
    ->aggregation('total_pageviews', [
        'value_count' => ['field' => '_id']
    ])
    ->aggregation('unique_visitors', [
        'cardinality' => ['field' => 'user_id']
    ])
    ->aggregation('daily_stats', [
        'date_histogram' => [
            'field' => 'timestamp',
            'calendar_interval' => 'day',
            'format' => 'yyyy-MM-dd'
        ],
        'aggs' => [
            'pageviews' => [
                'value_count' => ['field' => '_id']
            ],
            'unique_users' => [
                'cardinality' => ['field' => 'user_id']
            ],
            'avg_session_duration' => [
                'avg' => ['field' => 'session_duration']
            ],
            'bounce_rate' => [
                'avg' => ['field' => 'bounced']
            ]
        ]
    ])
    ->aggregation('device_breakdown', [
        'terms' => ['field' => 'device_type.keyword', 'size' => 10],
        'aggs' => [
            'avg_duration' => ['avg' => ['field' => 'session_duration']],
            'conversion_rate' => ['avg' => ['field' => 'converted']]
        ]
    ])
    ->aggregation('top_pages', [
        'terms' => ['field' => 'page_url.keyword', 'size' => 20],
        'aggs' => [
            'unique_visitors' => ['cardinality' => ['field' => 'user_id']]
        ]
    ])
    ->size(0); // No documents, only aggregations

echo "✅ Website analytics query built successfully\n";
echo "Period: Full year 2024\n";
echo "Metrics: pageviews, unique visitors, session duration, bounce rate\n";
echo "Breakdowns: daily, device type, top pages\n\n";

// E-commerce sales analytics
echo "2. E-commerce sales analytics with revenue breakdown:\n";
$salesQuery = new ElasticQuery();
$salesAnalytics = $salesQuery
    ->where('order_status', 'completed')
    ->whereBetween('order_date', [date('Y-m-01'), date('Y-m-t')])
    ->where('payment_status', 'paid')
    ->aggregation('total_revenue', [
        'sum' => ['field' => 'total_amount']
    ])
    ->aggregation('total_orders', [
        'value_count' => ['field' => '_id']
    ])
    ->aggregation('avg_order_value', [
        'avg' => ['field' => 'total_amount']
    ])
    ->aggregation('daily_sales', [
        'date_histogram' => [
            'field' => 'order_date',
            'calendar_interval' => 'day'
        ],
        'aggs' => [
            'revenue' => ['sum' => ['field' => 'total_amount']],
            'orders' => ['value_count' => ['field' => '_id']],
            'avg_order_value' => ['avg' => ['field' => 'total_amount']]
        ]
    ])
    ->aggregation('revenue_by_category', [
        'nested' => ['path' => 'items'],
        'aggs' => [
            'categories' => [
                'terms' => ['field' => 'items.category.keyword', 'size' => 15],
                'aggs' => [
                    'revenue' => ['sum' => ['field' => 'items.price']],
                    'quantity' => ['sum' => ['field' => 'items.quantity']]
                ]
            ]
        ]
    ])
    ->aggregation('customer_segments', [
        'range' => [
            'field' => 'total_amount',
            'ranges' => [
                ['key' => 'low_value', 'to' => 50],
                ['key' => 'medium_value', 'from' => 50, 'to' => 200],
                ['key' => 'high_value', 'from' => 200, 'to' => 500],
                ['key' => 'vip', 'from' => 500]
            ]
        ],
        'aggs' => [
            'customer_count' => ['cardinality' => ['field' => 'customer_id']]
        ]
    ])
    ->aggregation('payment_methods', [
        'terms' => ['field' => 'payment_method.keyword'],
        'aggs' => [
            'revenue' => ['sum' => ['field' => 'total_amount']],
            'avg_amount' => ['avg' => ['field' => 'total_amount']]
        ]
    ])
    ->size(0);

echo "✅ E-commerce sales analytics built successfully\n";
echo "Period: Current month\n";
echo "Metrics: revenue, orders, AOV, customer segments\n";
echo "Breakdowns: daily, category, customer value, payment methods\n\n";

// Content performance analytics
echo "3. Content performance and engagement analytics:\n";
$contentQuery = new ElasticQuery();
$contentAnalytics = $contentQuery
    ->where('content_type', 'article')
    ->where('status', 'published')
    ->whereBetween('published_date', [date('Y-m-d', strtotime('-90 days')), date('Y-m-d')])
    ->aggregation('total_articles', [
        'value_count' => ['field' => '_id']
    ])
    ->aggregation('engagement_stats', [
        'stats' => ['field' => 'engagement_score']
    ])
    ->aggregation('weekly_publishing', [
        'date_histogram' => [
            'field' => 'published_date',
            'calendar_interval' => 'week'
        ],
        'aggs' => [
            'articles_published' => ['value_count' => ['field' => '_id']],
            'avg_views' => ['avg' => ['field' => 'view_count']],
            'total_shares' => ['sum' => ['field' => 'share_count']],
            'avg_engagement' => ['avg' => ['field' => 'engagement_score']]
        ]
    ])
    ->aggregation('author_performance', [
        'terms' => ['field' => 'author.keyword', 'size' => 10],
        'aggs' => [
            'articles' => ['value_count' => ['field' => '_id']],
            'avg_views' => ['avg' => ['field' => 'view_count']],
            'total_shares' => ['sum' => ['field' => 'share_count']],
            'avg_reading_time' => ['avg' => ['field' => 'reading_time']],
            'engagement_score' => ['avg' => ['field' => 'engagement_score']]
        ]
    ])
    ->aggregation('content_categories', [
        'terms' => ['field' => 'category.keyword', 'size' => 15],
        'aggs' => [
            'performance' => [
                'stats' => ['field' => 'engagement_score']
            ],
            'avg_reading_time' => ['avg' => ['field' => 'reading_time']],
            'completion_rate' => ['avg' => ['field' => 'completion_rate']]
        ]
    ])
    ->aggregation('reading_time_distribution', [
        'histogram' => [
            'field' => 'reading_time',
            'interval' => 2
        ],
        'aggs' => [
            'avg_engagement' => ['avg' => ['field' => 'engagement_score']]
        ]
    ])
    ->size(0);

echo "✅ Content performance analytics built successfully\n";
echo "Period: Last 90 days\n";
echo "Metrics: engagement, views, shares, reading time\n";
echo "Breakdowns: weekly trends, author performance, categories\n\n";

// Search analytics and user behavior
echo "4. Search analytics and user behavior patterns:\n";
$searchQuery = new ElasticQuery();
$searchAnalytics = $searchQuery
    ->where('event_type', 'search')
    ->whereBetween('timestamp', [date('Y-m-d', strtotime('-30 days')), date('Y-m-d')])
    ->whereNotNull('search_query')
    ->aggregation('total_searches', [
        'value_count' => ['field' => '_id']
    ])
    ->aggregation('unique_searchers', [
        'cardinality' => ['field' => 'user_id']
    ])
    ->aggregation('search_trends', [
        'date_histogram' => [
            'field' => 'timestamp',
            'calendar_interval' => 'day'
        ],
        'aggs' => [
            'searches' => ['value_count' => ['field' => '_id']],
            'zero_results' => [
                'filter' => ['term' => ['results_count' => 0]]
            ],
            'avg_results' => ['avg' => ['field' => 'results_count']],
            'avg_click_position' => ['avg' => ['field' => 'first_click_position']]
        ]
    ])
    ->aggregation('popular_queries', [
        'terms' => ['field' => 'search_query.keyword', 'size' => 50],
        'aggs' => [
            'avg_results' => ['avg' => ['field' => 'results_count']],
            'click_through_rate' => ['avg' => ['field' => 'clicked']],
            'zero_results_rate' => [
                'filter' => ['term' => ['results_count' => 0]]
            ]
        ]
    ])
    ->aggregation('search_categories', [
        'terms' => ['field' => 'category_filter.keyword', 'size' => 20],
        'aggs' => [
            'searches' => ['value_count' => ['field' => '_id']],
            'success_rate' => [
                'filter' => ['range' => ['results_count' => ['gt' => 0]]]
            ]
        ]
    ])
    ->aggregation('user_search_patterns', [
        'terms' => ['field' => 'user_id', 'size' => 1000],
        'aggs' => [
            'search_frequency' => ['value_count' => ['field' => '_id']],
            'avg_session_searches' => ['avg' => ['field' => 'session_search_count']]
        ]
    ])
    ->size(0);

echo "✅ Search analytics query built successfully\n";
echo "Period: Last 30 days\n";
echo "Metrics: search volume, CTR, zero results, popular queries\n";
echo "Breakdowns: daily trends, categories, user patterns\n\n";

echo "🎯 Analytics Features Demonstrated:\n";
echo "• Date histogram for time-series analysis\n";
echo "• Terms aggregations for categorical breakdowns\n";
echo "• Statistical aggregations (sum, avg, stats)\n";
echo "• Nested aggregations for complex data\n";
echo "• Range aggregations for customer segmentation\n";
echo "• Cardinality for unique count metrics\n";
echo "• Filter aggregations for conditional metrics\n";
echo "• Multi-level aggregation hierarchies\n\n";

echo "💡 Analytics Best Practices:\n";
echo "• Use size(0) when only aggregations are needed\n";
echo "• Combine multiple aggregation types for rich insights\n";
echo "• Use date histograms for trending analysis\n";
echo "• Apply filters to focus on relevant data\n";
echo "• Use cardinality for unique user/item counts\n";
echo "• Nest aggregations for detailed breakdowns\n";
echo "• Consider data volume when setting aggregation sizes\n";
