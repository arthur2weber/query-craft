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

/**
 * Validate that two values are equal
 */
if (!function_exists('validateEquals')) {
    function validateEquals($description, $expected, $actual, $message = '') {
        $result = $expected === $actual;
        $GLOBALS['test_total_count']++;
        
        if ($result) {
            echo "✅ $description\n";
            $GLOBALS['test_success_count']++;
        } else {
            echo "❌ $description\n";
            echo "   Expected: " . json_encode($expected, JSON_PRETTY_PRINT) . "\n";
            echo "   Actual: " . json_encode($actual, JSON_PRETTY_PRINT) . "\n";
            if ($message) {
                echo "   Message: $message\n";
            }
        }
        
        return $result;
    }
}

/**
 * Validate that a condition is true
 */
if (!function_exists('validateTrue')) {
    function validateTrue($description, $condition, $message = '') {
        $GLOBALS['test_total_count']++;
        
        if ($condition) {
            echo "✅ $description\n";
            $GLOBALS['test_success_count']++;
        } else {
            echo "❌ $description\n";
            if ($message) {
                echo "   Message: $message\n";
            }
        }
        
        return $condition;
    }
}

/**
 * Validate that a condition is false
 */
if (!function_exists('validateFalse')) {
    function validateFalse($description, $condition, $message = '') {
        return validateTrue($description, !$condition, $message);
    }
}

/**
 * Record a failed test
 */
if (!function_exists('recordFailedTest')) {
    function recordFailedTest($testName, $errorMessage) {
        $GLOBALS['test_total_count']++;
        echo "❌ $testName FAILED: $errorMessage\n";
    }
}
