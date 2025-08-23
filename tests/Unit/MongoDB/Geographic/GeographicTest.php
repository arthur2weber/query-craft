<?php
/**
 * MongoDB Geographic Test
 * 
 * Tests MongoDB geospatial functionality including
 * geo queries, geo indexing, and location-based operations.
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\MongoQuery;

echo "🧪 MONGODB GEOGRAPHIC: Running Tests\n";
echo str_repeat("=", 50) . "\n\n";

resetTestCounters();

try {
    // Test 1: Near Query (find locations within distance)
    echo "1. Near Query\n";
    $query = new MongoQuery();
    $result = $query
        ->from('restaurants')
        ->near('location', [
            'type' => 'Point',
            'coordinates' => [-74.006, 40.7128] // NYC coordinates
        ], [
            'maxDistance' => 1000, // 1km
            'spherical' => true
        ])
        ->toQuery();
    
    $expected = [
        [
            '$match' => [
                'location' => [
                    '$near' => [
                        '$geometry' => [
                            'type' => 'Point',
                            'coordinates' => [-74.006, 40.7128]
                        ],
                        '$maxDistance' => 1000
                    ]
                ]
            ]
        ]
    ];
    
    validateEquals(
        'Near query',
        $expected,
        $result,
        'Near query should create proper geographic pipeline'
    );

    // Test 2: Within Geometry Query
    echo "\n2. GeoWithin Query\n";
    $query = new MongoQuery();
    $result = $query
        ->from('venues')
        ->within('location', [
            'type' => 'Polygon',
            'coordinates' => [[
                [-74.1, 40.7],
                [-73.9, 40.7],
                [-73.9, 40.8],
                [-74.1, 40.8],
                [-74.1, 40.7]
            ]]
        ])
        ->toQuery();
    
    $expected = [
        [
            '$match' => [
                'location' => [
                    '$geoWithin' => [
                        '$geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => [[
                                [-74.1, 40.7],
                                [-73.9, 40.7],
                                [-73.9, 40.8],
                                [-74.1, 40.8],
                                [-74.1, 40.7]
                            ]]
                        ]
                    ]
                ]
            ]
        ]
    ];
    
    validateEquals(
        'GeoWithin query',
        $expected,
        $result,
        'GeoWithin should create proper polygon query'
    );

    // Test 3: GeoNear Aggregation
    echo "\n3. GeoNear Aggregation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('stores')
        ->geoNear([
            'near' => [
                'type' => 'Point',
                'coordinates' => [-73.98, 40.75]
            ],
            'distanceField' => 'distance',
            'maxDistance' => 5000,
            'spherical' => true,
            'query' => ['status' => 'open']
        ])
        ->toQuery();
    
    $expected = [
        [
            '$geoNear' => [
                'near' => [
                    'type' => 'Point',
                    'coordinates' => [-73.98, 40.75]
                ],
                'distanceField' => 'distance',
                'maxDistance' => 5000,
                'spherical' => true,
                'query' => ['status' => 'open']
            ]
        ]
    ];
    
    validateEquals(
        'GeoNear aggregation',
        $expected,
        $result,
        'GeoNear should create proper aggregation stage'
    );

    // Test 4: Complex Geographic Query with Filters
    echo "\n4. Complex Geographic Query\n";
    $query = new MongoQuery();
    $result = $query
        ->from('events')
        ->near('venue.location', [
            'type' => 'Point',
            'coordinates' => [-74.006, 40.7128]
        ], ['maxDistance' => 2000])
        ->match([
            'date' => ['$gte' => '2024-01-01'],
            'category' => 'concert',
            'price' => ['$lte' => 100]
        ])
        ->sortAggregation(['date' => 1])
        ->limitAggregation(20)
        ->toQuery();
    
    // Verify structure
    validateTrue(
        'Complex geo query structure',
        is_array($result) && count($result) >= 3,
        'Should have multiple aggregation stages'
    );
    
    validateTrue(
        'Complex geo query has near',
        isset($result[0]['$match']['venue.location']['$near']),
        'Should start with near query'
    );

    // Test 5: Distance Calculation in Aggregation
    echo "\n5. Distance Calculation\n";
    $query = new MongoQuery();
    $result = $query
        ->from('locations')
        ->addFields([
            'distance' => [
                '$geoNear' => [
                    'near' => [-74.006, 40.7128],
                    'distanceField' => 'distance',
                    'spherical' => true
                ]
            ]
        ])
        ->match(['distance' => ['$lte' => 1000]])
        ->toQuery();
    
    // Verify basic structure
    validateTrue(
        'Distance calculation structure',
        is_array($result) && count($result) >= 2,
        'Should have addFields and match stages'
    );

    // Test 6: JSON Validation for Geographic Queries
    echo "\n6. JSON Validation for Geographic Queries\n";
    $query = new MongoQuery();
    $result = $query
        ->from('geo_data')
        ->near('coordinates', [
            'type' => 'Point',
            'coordinates' => [0, 0]
        ])
        ->toQuery();
    
    // Validate JSON serialization
    $json = json_encode($result);
    $decoded = json_decode($json, true);
    
    validateEquals(
        'Geographic queries JSON validation',
        $result,
        $decoded,
        'Geographic queries should be JSON serializable'
    );

} catch (Exception $e) {
    recordFailedTest('MongoDB Geographic Test', $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
$results = printTestSummary('MongoDB Geographic Tests');
return $results;
