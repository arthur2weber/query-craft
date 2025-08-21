<?php
/**
 * Test Helper Functions
 * 
 * Shared functions for all test files to avoid redeclaration errors.
 * 
 * @package Arthur2weber\QueryCraft\Tests
 */

// Global test counters
$GLOBALS['test_success_count'] = 0;
$GLOBALS['test_total_count'] = 0;

/**
 * Run a test and track results
 * 
 * @param string $description Test description
 * @param bool $result Test result (true = pass, false = fail)
 */
if (!function_exists('runTest')) {
    function runTest($description, $result) {
        $GLOBALS['test_total_count']++;
        echo ($result ? "✅" : "❌") . " $description\n";
        if ($result) {
            $GLOBALS['test_success_count']++;
        }
    }
}

/**
 * Reset test counters
 */
if (!function_exists('resetTestCounters')) {
    function resetTestCounters() {
        $GLOBALS['test_success_count'] = 0;
        $GLOBALS['test_total_count'] = 0;
    }
}

/**
 * Get test results
 * 
 * @return array Test results with passed, total, and success rate
 */
if (!function_exists('getTestResults')) {
    function getTestResults() {
        $passed = $GLOBALS['test_success_count'];
        $total = $GLOBALS['test_total_count'];
        $successRate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
        
        return [
            'passed' => $passed,
            'total' => $total,
            'success_rate' => $successRate
        ];
    }
}

/**
 * Print test summary
 * 
 * @param string $testName Name of the test suite
 */
if (!function_exists('printTestSummary')) {
    function printTestSummary($testName) {
        $results = getTestResults();
        echo "\n" . str_repeat("=", max(40, strlen($testName) + 10)) . "\n";
        echo "📊 $testName: {$results['passed']}/{$results['total']} passed\n";
        echo "📈 Success Rate: {$results['success_rate']}%\n";
        return $results;
    }
}
