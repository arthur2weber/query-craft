<?php
/**
 * Developer Experience Features Tests
 * 
 * Tests for verbose mode, performance warnings, and error translation features.
 * 
 * @package Arthur2weber\QueryCraft\Tests\Unit\Core
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\ElasticQuery;

echo "🧪 CORE: Developer Experience Tests\n";
echo str_repeat("=", 40) . "\n\n";

resetTestCounters();

// Test 1: Verbose mode activation
$query = new ElasticQuery();
$query->verbose();
runTest("Verbose mode activation", $query instanceof ElasticQuery);

// Test 2: Verbose logging
$query = new ElasticQuery();
$query->verbose();
$query->match('title', 'test');
$logs = $query->getVerboseLog();
runTest("Verbose logging returns array", is_array($logs));
runTest("Verbose logs contain entries", count($logs) > 0);

// Test 3: Performance warnings
$query = new ElasticQuery();
$query->verbose();
$query->wildcard('title', '*test*');
$warnings = $query->getPerformanceWarnings();
runTest("Performance warnings are array", is_array($warnings));
runTest("Wildcard triggers performance warning", count($warnings) > 0);

// Test 4: Size warnings
$query = new ElasticQuery();
$query->verbose();
$query->size(50000);
$warnings = $query->getPerformanceWarnings();
runTest("Large size triggers warning", count($warnings) > 0);

// Test 5: Deep pagination warnings
$query = new ElasticQuery();
$query->verbose();
$query->size(1000)->from(9500); // 9500 + 1000 = 10500 > 10000
$warnings = $query->getPerformanceWarnings();
runTest("Deep pagination triggers warning", count($warnings) > 0);

// Test 6: Regex warnings
$query = new ElasticQuery();
$query->verbose();
$query->regexp('content', '.*test.*');
$warnings = $query->getPerformanceWarnings();
runTest("Regex query triggers warning", count($warnings) > 0);

// Test 7: General warnings
$query = new ElasticQuery();
$query->verbose();
$query->wildcard('title', '*test*');
$allWarnings = $query->getWarnings();
runTest("getWarnings includes performance warnings", count($allWarnings) > 0);

// Test 8: Error translation - Index not found
$query = new ElasticQuery();
$error = 'index_not_found_exception: no such index [missing_products]';
$translated = $query->translateError($error);
runTest("Error translation returns string", is_string($translated));
runTest("Translation contains helpful message", strpos($translated, 'missing_products') !== false);

// Test 9: Error translation - Timeout
$query = new ElasticQuery();
$error = 'search_phase_execution_exception.*timeout';
$translated = $query->translateError($error);
runTest("Timeout error translation", strpos($translated, 'timed out') !== false);

// Test 10: Error translation - Security
$query = new ElasticQuery();
$error = 'security_exception: action [indices:data/read/search] is unauthorized';
$translated = $query->translateError($error);
runTest("Security error translation", strpos($translated, 'permission') !== false);

// Test 11: Error translation - Request timeout
$query = new ElasticQuery();
$error = 'Request timeout after 30000ms';
$translated = $query->translateError($error);
runTest("Request timeout translation", strpos($translated, 'timeout') !== false);

// Test 12: Method chaining with verbose
$query = new ElasticQuery();
$result = $query->verbose()->match('title', 'test')->size(10);
runTest("Method chaining with verbose", $result instanceof ElasticQuery);

// Test 13: Verbose logging with match method
$query = new ElasticQuery();
$query->verbose();
$query->match('title', 'PHP Elasticsearch');
$logs = $query->getVerboseLog();
$hasMatchLog = false;
foreach ($logs as $log) {
    if (strpos($log, 'Added match query') !== false) {
        $hasMatchLog = true;
        break;
    }
}
runTest("Match method logs verbose message", $hasMatchLog);

// Test 14: Verbose logging with filter
$query = new ElasticQuery();
$query->verbose();
$query->filter('status', 'published');
$logs = $query->getVerboseLog();
runTest("Filter operations work with verbose", count($logs) >= 1);

// Test 15: Verbose logging with range
$query = new ElasticQuery();
$query->verbose();
$query->range('created_at', 'gte', '2024-01-01');
$logs = $query->getVerboseLog();
runTest("Range operations work with verbose", count($logs) >= 1);

// Test 16: Verbose logging with sort
$query = new ElasticQuery();
$query->verbose();
$query->sort('created_at', 'desc');
$logs = $query->getVerboseLog();
runTest("Sort operations work with verbose", count($logs) >= 1);

// Test 17: Verbose logging with size
$query = new ElasticQuery();
$query->verbose();
$query->size(10);
$logs = $query->getVerboseLog();
$hasSizeLog = false;
foreach ($logs as $log) {
    if (strpos($log, 'Set result size') !== false) {
        $hasSizeLog = true;
        break;
    }
}
runTest("Size method logs verbose message", $hasSizeLog);

// Test 18: Verbose logging with from (pagination)
$query = new ElasticQuery();
$query->verbose();
$query->from(20);
$logs = $query->getVerboseLog();
$hasFromLog = false;
foreach ($logs as $log) {
    if (strpos($log, 'Set result offset') !== false) {
        $hasFromLog = true;
        break;
    }
}
runTest("From method logs verbose message", $hasFromLog);

// Test 19: Verbose logging with paginate
$query = new ElasticQuery();
$query->verbose();
$query->paginate(20, 2);
$logs = $query->getVerboseLog();
$hasPaginateLog = false;
foreach ($logs as $log) {
    if (strpos($log, 'Set pagination') !== false) {
        $hasPaginateLog = true;
        break;
    }
}
runTest("Paginate method logs verbose message", $hasPaginateLog);

// Test 20: Verbose logging with search
$query = new ElasticQuery();
$query->verbose();
$query->search('PHP Laravel', ['title', 'content']);
$logs = $query->getVerboseLog();
$hasSearchLog = false;
foreach ($logs as $log) {
    if (strpos($log, 'multi-match search') !== false) {
        $hasSearchLog = true;
        break;
    }
}
runTest("Search method logs verbose message", $hasSearchLog);

// Test 21: Complex query with multiple verbose logs
$query = new ElasticQuery();
$query->verbose();
$query
    ->match('title', 'PHP Tutorial')
    ->filter('status', 'published')
    ->range('created_at', 'gte', '2024-01-01')
    ->sort('created_at', 'desc')
    ->size(10);
$logs = $query->getVerboseLog();
runTest("Complex query generates multiple logs", count($logs) >= 5);

// Test 22: Verbose mode disable
$query = new ElasticQuery();
$query->verbose(false);
$query->match('title', 'test');
$logs = $query->getVerboseLog();
runTest("Verbose mode can be disabled", count($logs) === 0);

// Test 23: Performance warnings accumulate
$query = new ElasticQuery();
$query->verbose();
$query->wildcard('title', '*test*');
$query->size(50000);
$query->from(8000);
$warnings = $query->getPerformanceWarnings();
runTest("Multiple performance warnings accumulate", count($warnings) >= 3);

// Test 24: Wildcard without leading asterisk
$query = new ElasticQuery();
$query->verbose();
$query->wildcard('title', 'test*');
$warnings = $query->getPerformanceWarnings();
runTest("Trailing wildcard doesn't trigger leading warning", count($warnings) === 0);

// Test 25: Error translation with fallback
$query = new ElasticQuery();
$error = 'unknown_error_type: something went wrong';
$translated = $query->translateError($error);
runTest("Unknown error gets fallback translation", strpos($translated, 'common solutions') !== false);

return printTestSummary("Developer Experience Tests");
