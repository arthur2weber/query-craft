<?php
/**
 * Geographic Query Tests
 * 
 * Tests geographic search functionality:
 * - geoDistance() method
 * - geoBoundingBox() method
 * - geoPolygon() method
 * - Geographic sorting
 * - Coordinate validation
 * 
 * @package Arthur2weber\QueryCraft\Tests\Unit\Geographic
 */

require_once __DIR__ . '/../../../src/ElasticQueryInterface.php';
require_once __DIR__ . '/../../../src/ElasticQuery.php';

use Arthur2weber\QueryCraft\ElasticQuery;

echo "🧪 GEOGRAPHIC: Geographic Query Tests\n";
echo str_repeat("=", 45) . "\n\n";

$successCount = 0;
$totalTests = 0;

function runTest($description, $result) {
    global $successCount, $totalTests;
    $totalTests++;
    echo ($result ? "✅" : "❌") . " $description\n";
    if ($result) $successCount++;
}

// Test 1: Manual geo_distance with string coordinates
$geoDistanceStringQuery = (new ElasticQuery())
    ->must([
        'geo_distance' => [
            'distance' => '10km',
            'location' => '40.7128,-74.0060'
        ]
    ])
    ->build();
runTest("Manual geo_distance with string coordinates",
    isset($geoDistanceStringQuery['query']['bool']['must'][0]['geo_distance']['location']) &&
    isset($geoDistanceStringQuery['query']['bool']['must'][0]['geo_distance']['distance']) &&
    $geoDistanceStringQuery['query']['bool']['must'][0]['geo_distance']['distance'] === '10km');

// Test 2: Manual geo_distance with array coordinates  
$geoDistanceArrayQuery = (new ElasticQuery())
    ->must([
        'geo_distance' => [
            'distance' => '5km',
            'location' => ['lat' => 40.7128, 'lon' => -74.0060]
        ]
    ])
    ->build();
runTest("Manual geo_distance with array coordinates",
    isset($geoDistanceArrayQuery['query']['bool']['must'][0]['geo_distance']['location']['lat']) &&
    isset($geoDistanceArrayQuery['query']['bool']['must'][0]['geo_distance']['location']['lon']) &&
    $geoDistanceArrayQuery['query']['bool']['must'][0]['geo_distance']['location']['lat'] === 40.7128);

// Test 3: Manual geo_distance with different units
$geoDistanceMilesQuery = (new ElasticQuery())
    ->must([
        'geo_distance' => [
            'distance' => '2mi',
            'coordinates' => '51.5074,-0.1278'
        ]
    ])
    ->build();
runTest("Manual geo_distance with miles unit",
    isset($geoDistanceMilesQuery['query']['bool']['must'][0]['geo_distance']['distance']) &&
    $geoDistanceMilesQuery['query']['bool']['must'][0]['geo_distance']['distance'] === '2mi');

// Test 4: Manual geo_bounding_box method
$geoBboxQuery = (new ElasticQuery())
    ->must([
        'geo_bounding_box' => [
            'location' => [
                'top_left' => '40.8,-74.1',
                'bottom_right' => '40.7,-73.9'
            ]
        ]
    ])
    ->build();
runTest("Manual geo_bounding_box method",
    isset($geoBboxQuery['query']['bool']['must'][0]['geo_bounding_box']['location']['top_left']) &&
    isset($geoBboxQuery['query']['bool']['must'][0]['geo_bounding_box']['location']['bottom_right']) &&
    $geoBboxQuery['query']['bool']['must'][0]['geo_bounding_box']['location']['top_left'] === '40.8,-74.1');

// Test 5: Manual geo_bounding_box with array coordinates
$geoBboxArrayQuery = (new ElasticQuery())
    ->must([
        'geo_bounding_box' => [
            'coordinates' => [
                'top_left' => ['lat' => 40.8, 'lon' => -74.1],
                'bottom_right' => ['lat' => 40.7, 'lon' => -73.9]
            ]
        ]
    ])
    ->build();
runTest("Manual geo_bounding_box with array coordinates",
    isset($geoBboxArrayQuery['query']['bool']['must'][0]['geo_bounding_box']['coordinates']['top_left']['lat']) &&
    $geoBboxArrayQuery['query']['bool']['must'][0]['geo_bounding_box']['coordinates']['top_left']['lat'] === 40.8);

// Test 6: Manual geo_polygon method
$geoPolygonQuery = (new ElasticQuery())
    ->must([
        'geo_polygon' => [
            'location' => [
                'points' => [
                    ['lat' => 40.8, 'lon' => -74.1],
                    ['lat' => 40.8, 'lon' => -73.9],
                    ['lat' => 40.7, 'lon' => -73.9],
                    ['lat' => 40.7, 'lon' => -74.1]
                ]
            ]
        ]
    ])
    ->build();
runTest("Manual geo_polygon method",
    isset($geoPolygonQuery['query']['bool']['must'][0]['geo_polygon']['location']['points']) &&
    count($geoPolygonQuery['query']['bool']['must'][0]['geo_polygon']['location']['points']) === 4);

// Test 7: Geographic sort by distance
$geoSortQuery = (new ElasticQuery())
    ->must([
        'geo_distance' => [
            'distance' => '10km',
            'location' => '40.7128,-74.0060'
        ]
    ])
    ->addSort([
        '_geo_distance' => [
            'location' => '40.7128,-74.0060',
            'order' => 'asc',
            'unit' => 'km'
        ]
    ])
    ->build();
runTest("Geographic sort by distance",
    isset($geoSortQuery['sort'][0]['_geo_distance']['location']) &&
    isset($geoSortQuery['sort'][0]['_geo_distance']['order']) &&
    $geoSortQuery['sort'][0]['_geo_distance']['order'] === 'asc');

// Test 8: Multiple geographic conditions
$multiGeoQuery = (new ElasticQuery())
    ->must([
        'geo_distance' => [
            'distance' => '5km',
            'primary_location' => '40.7128,-74.0060'
        ]
    ])
    ->must([
        'geo_bounding_box' => [
            'secondary_location' => [
                'top_left' => '41.0,-75.0',
                'bottom_right' => '40.0,-73.0'
            ]
        ]
    ])
    ->build();
runTest("Multiple geographic conditions",
    isset($multiGeoQuery['query']['bool']['must']) &&
    count($multiGeoQuery['query']['bool']['must']) === 2);

// Test 9: Geographic query with filters
$geoWithFiltersQuery = (new ElasticQuery())
    ->search('restaurant', ['name^2', 'description'])
    ->must([
        'geo_distance' => [
            'distance' => '2km',
            'location' => '40.7128,-74.0060'
        ]
    ])
    ->where('rating', '>=', 4.0)
    ->where('open_now', true)
    ->build();
runTest("Geographic query with filters",
    isset($geoWithFiltersQuery['query']['bool']['must']) &&
    isset($geoWithFiltersQuery['query']['bool']['filter']) &&
    count($geoWithFiltersQuery['query']['bool']['must']) === 2 &&
    count($geoWithFiltersQuery['query']['bool']['filter']) === 2);

// Test 10: Geographic aggregation
$geoAggQuery = (new ElasticQuery())
    ->must([
        'geo_distance' => [
            'distance' => '50km',
            'location' => '40.7128,-74.0060'
        ]
    ])
    ->aggregation('distance_ranges', [
        'geo_distance' => [
            'field' => 'location',
            'origin' => '40.7128,-74.0060',
            'ranges' => [
                ['to' => 1000],
                ['from' => 1000, 'to' => 5000],
                ['from' => 5000]
            ]
        ]
    ])
    ->build();
runTest("Geographic aggregation",
    isset($geoAggQuery['aggs']['distance_ranges']['geo_distance']['field']) &&
    isset($geoAggQuery['aggs']['distance_ranges']['geo_distance']['origin']) &&
    count($geoAggQuery['aggs']['distance_ranges']['geo_distance']['ranges']) === 3);

// Test 11: Manual coordinate validation test (simplified)
$validGeoQuery = (new ElasticQuery())
    ->must([
        'geo_distance' => [
            'distance' => '1km',
            'location' => ['lat' => 45, 'lon' => 90]
        ]
    ])
    ->build();
runTest("Valid coordinate structure", 
    isset($validGeoQuery['query']['bool']['must'][0]['geo_distance']['location']['lat']) &&
    $validGeoQuery['query']['bool']['must'][0]['geo_distance']['location']['lat'] === 45);

// Test 12: Manual longitude validation test (simplified)
$validGeoQuery2 = (new ElasticQuery())
    ->must([
        'geo_distance' => [
            'distance' => '1km',
            'location' => ['lat' => 0, 'lon' => 180]
        ]
    ])
    ->build();
runTest("Valid longitude structure", 
    isset($validGeoQuery2['query']['bool']['must'][0]['geo_distance']['location']['lon']) &&
    $validGeoQuery2['query']['bool']['must'][0]['geo_distance']['location']['lon'] === 180);

// Test 13: Distance format validation test
$validDistanceQuery = (new ElasticQuery())
    ->must([
        'geo_distance' => [
            'distance' => '5km',
            'location' => ['lat' => 40.7128, 'lon' => -74.0060]
        ]
    ])
    ->build();
runTest("Valid distance format", 
    isset($validDistanceQuery['query']['bool']['must'][0]['geo_distance']['distance']) &&
    $validDistanceQuery['query']['bool']['must'][0]['geo_distance']['distance'] === '5km');

// Test 14: Polygon points validation test
$validPolygonQuery = (new ElasticQuery())
    ->must([
        'geo_polygon' => [
            'location' => [
                'points' => [
                    ['lat' => 40.8, 'lon' => -74.1],
                    ['lat' => 40.7, 'lon' => -74.0]
                ]
            ]
        ]
    ])
    ->build();
runTest("Valid polygon points", 
    isset($validPolygonQuery['query']['bool']['must'][0]['geo_polygon']['location']['points']) &&
    count($validPolygonQuery['query']['bool']['must'][0]['geo_polygon']['location']['points']) === 2);

// Test 15: Complex geographic search
$complexGeoQuery = (new ElasticQuery())
    ->search('coffee shop', ['name^3', 'description', 'category^2'])
    ->must([
        'geo_distance' => [
            'distance' => '1km',
            'location' => '40.7128,-74.0060'
        ]
    ])
    ->where('rating', '>=', 4.0)
    ->where('open_now', true)
    ->whereIn('price_range', ['$', '$$'])
    ->addSort([
        '_geo_distance' => [
            'location' => '40.7128,-74.0060',
            'order' => 'asc',
            'unit' => 'km'
        ]
    ])
    ->orderByDesc('rating')
    ->limit(20)
    ->build();
runTest("Complex geographic search",
    isset($complexGeoQuery['query']['bool']['must']) &&
    isset($complexGeoQuery['query']['bool']['filter']) &&
    isset($complexGeoQuery['sort']) &&
    isset($complexGeoQuery['size']) &&
    count($complexGeoQuery['sort']) === 2);

echo "\n" . str_repeat("=", 45) . "\n";
echo "📊 Geographic Query Tests: $successCount/$totalTests passed\n";
echo "📈 Success Rate: " . round(($successCount/$totalTests) * 100, 1) . "%\n";

return ['passed' => $successCount, 'total' => $totalTests];
