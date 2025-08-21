<?php
/**
 * E-commerce Product Search Example
 * 
 * This example demonstrates how to build a complete e-commerce product search
 * with filters, price ranges, brand facets, and inventory checks.
 * 
 * Features demonstrated:
 * - Product name and description search
 * - Price range filtering
 * - Brand and category facets
 * - Inventory and availability checks
 * - Rating and review filtering
 * - Sorting by relevance, price, and popularity
 * 
 * @package Arthur2weber\QueryCraft\Examples
 */

require_once __DIR__ . '/../src/ElasticQueryInterface.php';
require_once __DIR__ . '/../src/ElasticQuery.php';

use Arthur2weber\QueryCraft\ElasticQuery;

echo "🛒 E-commerce Product Search Example\n";
echo str_repeat("=", 50) . "\n\n";

// Basic product search
echo "1. Basic product search with price and availability filters:\n";
$productQuery = new ElasticQuery();
$basicProductSearch = $productQuery
    ->search('wireless bluetooth headphones', ['name^3', 'description', 'brand^2'])
    ->where('status', 'active')
    ->where('in_stock', true)
    ->where('stock_quantity', '>', 0)
    ->whereBetween('price', [50, 500])
    ->where('rating', '>=', 3.5)
    ->orderByDesc('_score')
    ->orderByDesc('rating')
    ->paginate(24, 1);

echo "✅ Basic product search query built successfully\n";
echo "Search: wireless bluetooth headphones\n";
echo "Price range: $50 - $500\n";
echo "Minimum rating: 3.5 stars\n\n";

// Advanced product search with facets
echo "2. Advanced product search with brand and category facets:\n";
$advancedProductQuery = new ElasticQuery();
$advancedProductSearch = $advancedProductQuery
    ->search('smartphone android', ['name^5', 'description^2', 'brand^3', 'model^2'])
    ->where('status', 'active')
    ->where('in_stock', true)
    ->whereBetween('price', [200, 1200])
    ->where('rating', '>=', 4)
    ->where('review_count', '>=', 10)
    ->whereIn('brand', ['samsung', 'google', 'oneplus', 'xiaomi'])
    ->whereIn('storage', ['128GB', '256GB', '512GB'])
    ->where('discount_percentage', '>', 0) // On sale items
    ->whereNull('requires_subscription')
    ->whereNotIn('condition', ['refurbished', 'open-box'])
    ->aggregation('brands', [
        'terms' => ['field' => 'brand.keyword', 'size' => 15]
    ])
    ->aggregation('price_ranges', [
        'range' => [
            'field' => 'price',
            'ranges' => [
                ['key' => 'budget', 'to' => 300],
                ['key' => 'mid-range', 'from' => 300, 'to' => 700],
                ['key' => 'premium', 'from' => 700, 'to' => 1000],
                ['key' => 'flagship', 'from' => 1000]
            ]
        ]
    ])
    ->aggregation('storage_options', [
        'terms' => ['field' => 'storage.keyword', 'size' => 10]
    ])
    ->aggregation('avg_rating', [
        'avg' => ['field' => 'rating']
    ])
    ->orderByDesc('_score')
    ->orderByDesc('popularity_score')
    ->orderBy('price', 'asc')
    ->paginate(20);

echo "✅ Advanced product search with facets built successfully\n";
echo "Aggregations: brands, price_ranges, storage_options, avg_rating\n\n";

// Product search with geographic availability
echo "3. Product search with local availability:\n";
$localProductQuery = new ElasticQuery();
$localProductSearch = $localProductQuery
    ->search('gaming laptop RTX', ['name^3', 'description^1', 'specifications^2'])
    ->where('status', 'active')
    ->where('in_stock', true)
    ->whereBetween('price', [800, 3000])
    ->where('rating', '>=', 4)
    ->must([
        'nested' => [
            'path' => 'availability',
            'query' => [
                'bool' => [
                    'must' => [
                        ['term' => ['availability.in_stock' => true]],
                        ['geo_distance' => [
                            'distance' => '25km',
                            'availability.store_location' => [
                                'lat' => 40.7128,
                                'lon' => -74.0060
                            ]
                        ]]
                    ]
                ]
            ]
        ]
    ])
    ->aggregation('local_stores', [
        'nested' => [
            'path' => 'availability'
        ],
        'aggs' => [
            'nearby_stores' => [
                'filter' => [
                    'geo_distance' => [
                        'distance' => '25km',
                        'availability.store_location' => [
                            'lat' => 40.7128,
                            'lon' => -74.0060
                        ]
                    ]
                ],
                'aggs' => [
                    'store_names' => [
                        'terms' => ['field' => 'availability.store_name.keyword']
                    ]
                ]
            ]
        ]
    ])
    ->orderByDesc('_score')
    ->limit(15);

echo "✅ Local product search built successfully\n";
echo "Search radius: 25km from NYC\n";
echo "Category: Gaming laptops with RTX\n\n";

// Flash sale and promotional search
echo "4. Flash sale and promotional product search:\n";
$saleProductQuery = new ElasticQuery();
$saleProductSearch = $saleProductQuery
    ->where('status', 'active')
    ->where('in_stock', true)
    ->where('discount_percentage', '>=', 20) // At least 20% off
    ->whereBetween('sale_end_date', [date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('+7 days'))])
    ->should(ElasticQuery::termBoostClause('flash_sale', true, 3.0))
    ->should(ElasticQuery::termBoostClause('limited_time', true, 2.0))
    ->should(ElasticQuery::rangeBoostClause('discount_percentage', 'gte', 50, 2.5))
    ->aggregation('discount_ranges', [
        'range' => [
            'field' => 'discount_percentage',
            'ranges' => [
                ['key' => '20-30%', 'from' => 20, 'to' => 30],
                ['key' => '30-50%', 'from' => 30, 'to' => 50],
                ['key' => '50%+', 'from' => 50]
            ]
        ]
    ])
    ->aggregation('sale_categories', [
        'terms' => ['field' => 'category.keyword', 'size' => 10]
    ])
    ->orderByDesc('discount_percentage')
    ->orderByDesc('_score')
    ->limit(30);

echo "✅ Flash sale search built successfully\n";
echo "Minimum discount: 20%\n";
echo "Sale period: Next 7 days\n\n";

echo "🎯 E-commerce Features Demonstrated:\n";
echo "• Product name, brand, and description search\n";
echo "• Price range and discount filtering\n";
echo "• Inventory and availability checks\n";
echo "• Rating and review count filtering\n";
echo "• Brand and category facets\n";
echo "• Geographic store availability\n";
echo "• Promotional and sale queries\n";
echo "• Multi-level sorting (relevance, price, rating)\n";
echo "• Nested queries for complex data structures\n\n";

echo "💡 E-commerce Search Tips:\n";
echo "• Boost product names higher than descriptions\n";
echo "• Always filter by in_stock and status\n";
echo "• Use price ranges for better UX\n";
echo "• Include rating filters for quality\n";
echo "• Add brand and category aggregations\n";
echo "• Consider geographic availability for local stores\n";
echo "• Boost promotional items during sales\n";
