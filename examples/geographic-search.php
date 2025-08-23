<?php
/**
 * Geographic and Location-based Search Example
 * 
 * This example demonstrates how to build location-aware searches
 * using Elasticsearch's geographic capabilities.
 * 
 * Features demonstrated:
 * - geo_distance queries for radius search
 * - Geographic sorting by distance
 * - Location-based aggregations
 * - Bounding box searches
 * - Nearby business search
 * 
 * @package Arthur2weber\QueryCraft\Examples
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Arthur2weber\QueryCraft\Query\ElasticQuery;

echo "📍 Geographic Search Example\n";
echo str_repeat("=", 50) . "\n\n";

// Basic nearby restaurant search
echo "1. Find restaurants within 5km radius:\n";
$restaurantQuery = new ElasticQuery();
$nearbyRestaurants = $restaurantQuery
    ->search('italian restaurant', ['name^3', 'cuisine^2', 'description'])
    ->must([
        'geo_distance' => [
            'distance' => '5km',
            'location' => ['lat' => 40.7128, 'lon' => -74.0060] // NYC coordinates
        ]
    ])
    ->where('status', 'active')
    ->where('is_open', true)
    ->where('rating', '>=', 4)
    ->whereIn('cuisine_type', ['italian', 'mediterranean', 'european'])
    ->whereIn('price_range', ['$', '$$', '$$$'])
    ->whereNotIn('restrictions', ['closed', 'private-event'])
    ->sort('_score', 'desc')
    ->sort('_geo_distance', [
        'location' => ['lat' => 40.7128, 'lon' => -74.0060],
        'order' => 'asc',
        'unit' => 'km'
    ])
    ->limit(20);

echo "✅ Nearby restaurant search built successfully\n";
echo "Search radius: 5km from NYC (40.7128, -74.0060)\n";
echo "Cuisine: Italian and Mediterranean\n";
echo "Sorted by: relevance, then distance\n\n";

// Hotel search with distance aggregations
echo "2. Hotel search with distance-based grouping:\n";
$hotelQuery = new ElasticQuery();
$hotelSearch = $hotelQuery
    ->search('luxury hotel spa', ['name^3', 'amenities^2', 'description'])
    ->must([
        'geo_distance' => [
            'distance' => '25km',
            'location' => ['lat' => 51.5074, 'lon' => -0.1278] // London coordinates
        ]
    ])
    ->where('status', 'active')
    ->where('availability', true)
    ->whereBetween('rating', [4, 5])
    ->whereBetween('price_per_night', [100, 800])
    ->whereIn('amenities', ['spa', 'gym', 'pool', 'wifi'])
    ->aggregation('distance_ranges', [
        'geo_distance' => [
            'field' => 'location',
            'origin' => ['lat' => 51.5074, 'lon' => -0.1278],
            'ranges' => [
                ['key' => 'walking_distance', 'to' => 1],
                ['key' => 'nearby', 'from' => 1, 'to' => 5],
                ['key' => 'close', 'from' => 5, 'to' => 15],
                ['key' => 'accessible', 'from' => 15]
            ],
            'unit' => 'km'
        ]
    ])
    ->aggregation('price_by_distance', [
        'geo_distance' => [
            'field' => 'location',
            'origin' => ['lat' => 51.5074, 'lon' => -0.1278],
            'ranges' => [
                ['key' => 'center', 'to' => 5],
                ['key' => 'outskirts', 'from' => 5]
            ]
        ],
        'aggs' => [
            'avg_price' => [
                'avg' => ['field' => 'price_per_night']
            ]
        ]
    ])
    ->orderByDesc('rating')
    ->orderBy('_geo_distance')
    ->limit(15);

echo "✅ Hotel search with distance aggregations built successfully\n";
echo "Search radius: 25km from London\n";
echo "Aggregations: distance_ranges, price_by_distance\n\n";

// Bounding box search for real estate
echo "3. Real estate search within bounding box:\n";
$realEstateQuery = new ElasticQuery();
$realEstateSearch = $realEstateQuery
    ->search('apartment condo', ['title^3', 'description', 'neighborhood^2'])
    ->must([
        'geo_bounding_box' => [
            'location' => [
                'top_left' => ['lat' => 40.8, 'lon' => -74.1],
                'bottom_right' => ['lat' => 40.7, 'lon' => -73.9]
            ]
        ]
    ])
    ->where('status', 'available')
    ->where('property_type', 'apartment')
    ->whereBetween('price', [2000, 8000])
    ->whereBetween('bedrooms', [1, 4])
    ->where('pet_friendly', true)
    ->whereIn('amenities', ['parking', 'gym', 'doorman'])
    ->aggregation('neighborhoods', [
        'terms' => ['field' => 'neighborhood.keyword', 'size' => 10]
    ])
    ->aggregation('price_by_bedrooms', [
        'terms' => ['field' => 'bedrooms'],
        'aggs' => [
            'avg_price' => ['avg' => ['field' => 'price']],
            'min_price' => ['min' => ['field' => 'price']],
            'max_price' => ['max' => ['field' => 'price']]
        ]
    ])
    ->orderBy('price', 'asc')
    ->paginate(25);

echo "✅ Real estate bounding box search built successfully\n";
echo "Area: Manhattan bounding box\n";
echo "Price range: $2,000 - $8,000\n";
echo "Bedrooms: 1-4, Pet-friendly only\n\n";

// Service provider search with multiple locations
echo "4. Service provider search (businesses with multiple locations):\n";
$serviceQuery = new ElasticQuery();
$serviceSearch = $serviceQuery
    ->search('plumber emergency repair', ['services^3', 'company_name^2', 'description'])
    ->must([
        'nested' => [
            'path' => 'locations',
            'query' => [
                'geo_distance' => [
                    'distance' => '15km',
                    'locations.coordinates' => ['lat' => 34.0522, 'lon' => -118.2437] // LA coordinates
                ]
            ]
        ]
    ])
    ->where('status', 'active')
    ->where('available_24_7', true)
    ->where('rating', '>=', 4.5)
    ->where('response_time', '<=', 60) // minutes
    ->whereIn('services', ['plumbing', 'emergency_repair', 'water_damage'])
    ->aggregation('service_areas', [
        'nested' => ['path' => 'locations'],
        'aggs' => [
            'nearby_locations' => [
                'filter' => [
                    'geo_distance' => [
                        'distance' => '15km',
                        'locations.coordinates' => ['lat' => 34.0522, 'lon' => -118.2437]
                    ]
                ],
                'aggs' => [
                    'areas' => [
                        'terms' => ['field' => 'locations.area.keyword', 'size' => 10]
                    ]
                ]
            ]
        ]
    ])
    ->orderByDesc('rating')
    ->orderBy('response_time', 'asc')
    ->limit(10);

echo "✅ Multi-location service provider search built successfully\n";
echo "Search radius: 15km from Los Angeles\n";
echo "Services: Emergency plumbing repair\n";
echo "24/7 availability required\n\n";

echo "🎯 Geographic Search Features Demonstrated:\n";
echo "• geo_distance queries for radius-based search\n";
echo "• geo_bounding_box for area-based search\n";
echo "• Geographic sorting by distance\n";
echo "• Distance-based aggregations and grouping\n";
echo "• Nested geographic queries for complex data\n";
echo "• Multi-location business search\n";
echo "• Price and rating by distance analysis\n\n";

echo "💡 Geographic Search Tips:\n";
echo "• Always include distance in sorting for relevance\n";
echo "• Use appropriate radius for your use case\n";
echo "• Consider bounding box for area-specific searches\n";
echo "• Aggregate by distance ranges for better UX\n";
echo "• Use nested queries for multi-location businesses\n";
echo "• Include location context in search fields\n";
echo "• Consider time zones for service availability\n";
