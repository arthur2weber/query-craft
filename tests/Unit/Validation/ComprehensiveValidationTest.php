<?php
/**
 * Comprehensive Validation Tests
 * 
 * Tests all validation functionality implemented:
 * - Field name validation
 * - Value validation (numbers, strings, arrays)
 * - ADL syntax validation
 * - Geo coordinate validation
 * - Logical conflict detection
 * - Edge cases and error conditions
 * 
 * @package Arthur2weber\QueryCraft\Tests\Unit\Validation
 */

require_once __DIR__ . '/../../../src/ElasticQueryInterface.php';
require_once __DIR__ . '/../../../src/ElasticQuery.php';

use Arthur2weber\QueryCraft\ElasticQuery;

echo "🧪 VALIDATION: Comprehensive Validation Tests\n";
echo str_repeat("=", 50) . "\n\n";

$successCount = 0;
$totalTests = 0;

function runTest($description, $result) {
    global $successCount, $totalTests;
    $totalTests++;
    echo ($result ? "✅" : "❌") . " $description\n";
    if ($result) $successCount++;
}

// =============================================================================
// BASIC VALIDATION TESTS
// =============================================================================

// Test 1: Valid field names
try {
    $query = new ElasticQuery();
    $query->where('valid_field_name', 'value');
    runTest("Valid field name acceptance", true);
} catch (Exception $e) {
    runTest("Valid field name acceptance", false);
}

// Test 2: Empty field name validation
try {
    (new ElasticQuery())->where('', 'value');
    runTest("Empty field name rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Empty field name rejection", true);
}



// Test 4: Valid string values
try {
    $query = new ElasticQuery();
    $query->where('field', 'valid string value');
    runTest("Valid string value acceptance", true);
} catch (Exception $e) {
    runTest("Valid string value acceptance", false);
}

// Test 5: Valid numeric values
try {
    $query = new ElasticQuery();
    $query->where('field', 123);
    $query->where('field2', 123.45);
    runTest("Valid numeric value acceptance", true);
} catch (Exception $e) {
    runTest("Valid numeric value acceptance", false);
}

// =============================================================================
// ADL SYNTAX VALIDATION TESTS
// =============================================================================

// Test 6: Infinite number rejection
try {
    (new ElasticQuery())->range('field', 'gte', INF);
    runTest("Infinite number rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Infinite number rejection", true);
}

// Test 7: NaN rejection
try {
    (new ElasticQuery())->range('field', 'gte', NAN);
    runTest("NaN rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("NaN rejection", true);
}

// Test 8: Control characters in strings
try {
    (new ElasticQuery())->where('field', "test\x00control");
    runTest("Control characters rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Control characters rejection", true);
}

// Test 9: Non-UTF-8 strings (if mbstring available)
if (function_exists('mb_check_encoding')) {
    try {
        (new ElasticQuery())->where('field', "\x80\x81invalid");
        runTest("Non-UTF-8 strings rejection", false);
    } catch (\InvalidArgumentException $e) {
        runTest("Non-UTF-8 strings rejection", true);
    }
} else {
    runTest("Non-UTF-8 strings rejection", true); // Skip if mbstring not available
}

// Test 10: Empty arrays rejection
try {
    (new ElasticQuery())->whereIn('field', []);
    runTest("Empty arrays rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Empty arrays rejection", true);
}

// Test 11: Empty script rejection
try {
    (new ElasticQuery())->scriptScore('');
    runTest("Empty script rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Empty script rejection", true);
}

// Test 12: Empty aggregation rejection
try {
    (new ElasticQuery())->aggregation('test', []);
    runTest("Empty aggregation rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Empty aggregation rejection", true);
}

// Test 13: Empty nested path rejection
try {
    (new ElasticQuery())->nested('', function($q) { $q->where('test', 'value'); });
    runTest("Empty nested path rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Empty nested path rejection", true);
}

// Test 14: Empty highlight fields rejection
try {
    (new ElasticQuery())->highlight([]);
    runTest("Empty highlight fields rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Empty highlight fields rejection", true);
}

// =============================================================================
// LOGICAL VALIDATION TESTS
// =============================================================================

// Test 15: whereBetween min > max validation
try {
    (new ElasticQuery())->whereBetween('price', [500, 100]);
    runTest("whereBetween min > max rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("whereBetween min > max rejection", true);
}

// Test 16: minimum_should_match > should clauses
try {
    $query = new ElasticQuery();
    $query->should(['term' => ['status' => 'active']])
          ->should(['term' => ['featured' => true]])
          ->minimumShouldMatch(5); // More than 2 should clauses
    runTest("minimum_should_match > should clauses rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("minimum_should_match > should clauses rejection", true);
}

// Test 17: Valid minimum_should_match percentage
try {
    $query = new ElasticQuery();
    $query->should(['term' => ['status' => 'active']])
          ->minimumShouldMatch('75%');
    runTest("Valid percentage minimum_should_match", true);
} catch (Exception $e) {
    runTest("Valid percentage minimum_should_match", false);
}

// Test 18: Invalid minimum_should_match percentage > 100%
try {
    $query = new ElasticQuery();
    $query->should(['term' => ['status' => 'active']])
          ->minimumShouldMatch('150%');
    runTest("Invalid percentage > 100% rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Invalid percentage > 100% rejection", true);
}

// =============================================================================
// GEOGRAPHIC VALIDATION TESTS
// =============================================================================

// Test 19: Valid coordinates acceptance
try {
    $query = new ElasticQuery();
    $query->geoDistance('location', ['lat' => 40.7128, 'lon' => -74.0060], '10km');
    runTest("Valid coordinates acceptance", true);
} catch (Exception $e) {
    runTest("Valid coordinates acceptance", false);
}

// Test 20: Invalid latitude > 90
try {
    (new ElasticQuery())->geoDistance('location', ['lat' => 91, 'lon' => 0], '1km');
    runTest("Invalid latitude > 90 rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Invalid latitude > 90 rejection", true);
}

// Test 21: Invalid latitude < -90
try {
    (new ElasticQuery())->geoDistance('location', ['lat' => -91, 'lon' => 0], '1km');
    runTest("Invalid latitude < -90 rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Invalid latitude < -90 rejection", true);
}

// Test 22: Invalid longitude > 180
try {
    (new ElasticQuery())->geoDistance('location', ['lat' => 0, 'lon' => 181], '1km');
    runTest("Invalid longitude > 180 rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Invalid longitude > 180 rejection", true);
}

// Test 23: Invalid longitude < -180
try {
    (new ElasticQuery())->geoDistance('location', ['lat' => 0, 'lon' => -181], '1km');
    runTest("Invalid longitude < -180 rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Invalid longitude < -180 rejection", true);
}

// Test 24: Invalid geo_distance format
try {
    (new ElasticQuery())->must([
        'geo_distance' => [
            'distance' => 'invalid_format',
            'location' => ['lat' => 40.7128, 'lon' => -74.0060]
        ]
    ]);
    runTest("Invalid geo_distance format rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Invalid geo_distance format rejection", true);
}

// =============================================================================
// OPERATOR AND FORMAT VALIDATION TESTS
// =============================================================================

// Test 25: Invalid range operators
try {
    (new ElasticQuery())->range('field', 'invalid_op', 100);
    runTest("Invalid range operator rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Invalid range operator rejection", true);
}

// Test 26: Valid range operators
try {
    $query = new ElasticQuery();
    $query->range('field1', 'gte', 100)
          ->range('field2', 'gt', 200)
          ->range('field3', 'lte', 300)
          ->range('field4', 'lt', 400);
    runTest("Valid range operators acceptance", true);
} catch (Exception $e) {
    runTest("Valid range operators acceptance", false);
}

// Test 27: Invalid sort directions
try {
    (new ElasticQuery())->sort('field', 'invalid_direction');
    runTest("Invalid sort direction rejection", false);
} catch (\InvalidArgumentException $e) {
    runTest("Invalid sort direction rejection", true);
}

// Test 28: Valid sort directions
try {
    $query = new ElasticQuery();
    $query->sort('field1', 'asc')->sort('field2', 'desc');
    runTest("Valid sort directions acceptance", true);
} catch (Exception $e) {
    runTest("Valid sort directions acceptance", false);
}

// =============================================================================
// COMPLEX VALIDATION SCENARIOS
// =============================================================================

// Test 29: Deep pagination warning (should not throw, just warn)
try {
    $query = new ElasticQuery();
    $query->from(9000)->size(2000); // Total > 10000
    runTest("Deep pagination handling", true);
} catch (Exception $e) {
    runTest("Deep pagination handling", false);
}

// Test 30: Large size warning (should not throw, just warn)
try {
    $query = new ElasticQuery();
    $query->size(15000); // > 10000
    runTest("Large size handling", true);
} catch (Exception $e) {
    runTest("Large size handling", false);
}

// Test 31: Valid complex query (should pass all validations)
try {
    $query = new ElasticQuery();
    $result = $query
        ->search('Laravel tutorial', ['title^3', 'content'])
        ->where('status', 'published')
        ->range('rating', 'gte', 4.0)
        ->terms('tags', ['php', 'web', 'framework'])
        ->geoDistance('location', ['lat' => 40.7128, 'lon' => -74.0060], '10km')
        ->should(['term' => ['featured' => true]])
        ->should(['term' => ['trending' => true]])
        ->minimumShouldMatch(1)
        ->aggregation('avg_rating', ['avg' => ['field' => 'rating']])
        ->orderByDesc('_score')
        ->orderByDesc('created_at')
        ->paginate(20, 2)
        ->highlight(['title', 'content'])
        ->source(['id', 'title', 'summary', 'rating'])
        ->build();
    
    $requiredKeys = ['query', 'aggs', 'sort', 'size', 'from', 'highlight', '_source'];
    $allPresent = true;
    foreach ($requiredKeys as $key) {
        if (!isset($result[$key])) {
            $allPresent = false;
            break;
        }
    }
    runTest("Complex valid query acceptance", $allPresent);
} catch (Exception $e) {
    runTest("Complex valid query acceptance", false);
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 Comprehensive Validation Tests: $successCount/$totalTests passed\n";
echo "📈 Success Rate: " . round(($successCount/$totalTests) * 100, 1) . "%\n";

return ['passed' => $successCount, 'total' => $totalTests];
