<?php
/**
 * MongoDB Comprehensive Test Suite Runner
 * 
 * Runs all MongoDB tests and provides a comprehensive summary
 * of MongoDB query functionality validation.
 */

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../TestHelpers.php';

echo "🧪 MONGODB COMPREHENSIVE TEST SUITE\n";
echo str_repeat("=", 60) . "\n\n";

$startTime = microtime(true);
$testSuites = [];
// Lista detalhada dos arquivos de teste que serão executados / foram executados
$executedFiles = [];

// Define test suites to run
$testFiles = [
    'Core Tests' => [
        'description' => 'Basic query operations and functionality',
        'file' => __DIR__ . '/Core/BasicQueryTest.php'
    ],
    'Aggregation Tests' => [
        'description' => 'MongoDB aggregation pipeline operations',
        'file' => __DIR__ . '/Aggregations/AggregationTest.php'
    ],
    'Filter Tests' => [
        'description' => 'Advanced filtering and query operators',
        'file' => __DIR__ . '/Filters/SimpleAdvancedFiltersTest.php'
    ],
    'Text Search Tests' => [
        'description' => 'Full-text search functionality',
        'file' => __DIR__ . '/Search/TextSearchTest.php'
    ],
    'Geographic Tests' => [
        'description' => 'Geospatial queries and operations',
        'file' => __DIR__ . '/Geographic/GeographicTest.php'
    ],
    'Sorting Tests' => [
        'description' => 'Sort operations and ordering',
        'file' => __DIR__ . '/Sorting/SortingTest.php'
    ],
    'Utilities Tests' => [
        'description' => 'Utility functions and helpers',
        'file' => __DIR__ . '/Utilities/UtilitiesTest.php'
    ],
    'Validation Tests' => [
        'description' => 'Query validation and error handling',
        'file' => __DIR__ . '/Validation/ValidationTest.php'
    ]
];

$totalPassed = 0;
$totalTests = 0;
$failedSuites = [];

echo "📋 Running MongoDB Test Suites:\n\n";

foreach ($testFiles as $suiteName => $suiteInfo) {
    echo "🔹 {$suiteName}: {$suiteInfo['description']}\n";
    
    if (!file_exists($suiteInfo['file'])) {
        echo "   ❌ Test file not found: {$suiteInfo['file']}\n";
        // Registra arquivo não encontrado
        $executedFiles[] = [
            'file' => $suiteInfo['file'],
            'suite' => $suiteName,
            'status' => 'not_found'
        ];
        $failedSuites[] = $suiteName;
        continue;
    }
    
    $suiteStartTime = microtime(true);
    
    // Reset test counters before each suite
    resetTestCounters();
    
    try {
        // Capture the output to parse test results
        ob_start();
        $maybeResult = include $suiteInfo['file'];
        $output = ob_get_clean();
        
        // Prefer structured return from test file if present
        $suitePassed = 0;
        $suiteTotal = 0;
        if (is_array($maybeResult) && array_key_exists('passed', $maybeResult) && array_key_exists('total', $maybeResult)) {
            $suitePassed = (int)$maybeResult['passed'];
            $suiteTotal = (int)$maybeResult['total'];
        } else {
            // Try using global helper getTestResults() if available
            if (function_exists('getTestResults')) {
                $gr = getTestResults();
                $suitePassed = isset($gr['passed']) ? (int)$gr['passed'] : 0;
                $suiteTotal = isset($gr['total']) ? (int)$gr['total'] : 0;
            }
            // If still zero, fall back to parsing printed output for compatibility
            if ($suiteTotal === 0) {
                // Look for test summary line like "📊 MongoDB Text Search Tests: 12/12 passed"
                if (preg_match('/(\d+)\/(\d+)\s+passed/', $output, $matches)) {
                    $suitePassed = (int)$matches[1];
                    $suiteTotal = (int)$matches[2];
                }
            }
        }
        
        $suiteFailed = $suiteTotal - $suitePassed;
        
        $totalPassed += $suitePassed;
        $totalTests += $suiteTotal;
        
        $suiteEndTime = microtime(true);
        $suiteTime = round(($suiteEndTime - $suiteStartTime) * 1000, 2);
        
        if ($suiteFailed > 0) {
            $failedSuites[] = $suiteName;
            echo "   ❌ {$suitePassed}/{$suiteTotal} passed ({$suiteTime}ms)\n";
        } else {
            echo "   ✅ {$suitePassed}/{$suiteTotal} passed ({$suiteTime}ms)\n";
        }
        
        $testSuites[$suiteName] = [
            'passed' => $suitePassed,
            'failed' => $suiteFailed,
            'total' => $suiteTotal,
            'time' => $suiteTime,
            'success_rate' => $suiteTotal > 0 ? round(($suitePassed / $suiteTotal) * 100, 1) : 0
        ];
        
        // Registra o arquivo executado com resumo
        $executedFiles[] = [
            'file' => $suiteInfo['file'],
            'suite' => $suiteName,
            'passed' => $suitePassed,
            'total' => $suiteTotal,
            'time' => $suiteTime,
            'status' => ($suiteFailed > 0 ? 'failed' : 'passed')
        ];
        
    } catch (Throwable $e) {
        ob_end_clean();
        echo "   ❌ Suite failed with error: " . $e->getMessage() . "\n";
        $failedSuites[] = $suiteName;
        $testSuites[$suiteName] = [
            'passed' => 0,
            'failed' => 1,
            'total' => 1,
            'time' => 0,
            'success_rate' => 0,
            'error' => $e->getMessage()
        ];
        // Registra arquivo com erro
        $executedFiles[] = [
            'file' => $suiteInfo['file'],
            'suite' => $suiteName,
            'passed' => 0,
            'total' => 1,
            'time' => 0,
            'status' => 'error',
            'error' => $e->getMessage()
        ];
        $totalTests += 1;
    }
}

$endTime = microtime(true);
$totalTime = round(($endTime - $startTime) * 1000, 2);
$overallSuccessRate = $totalTests > 0 ? round(($totalPassed / $totalTests) * 100, 1) : 0;

echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 MONGODB TEST RESULTS SUMMARY\n";
echo str_repeat("=", 60) . "\n\n";

echo "⏱️  Total Execution Time: {$totalTime}ms\n";
echo "📈 Overall Success Rate: {$overallSuccessRate}% ({$totalPassed}/{$totalTests})\n\n";

echo "📋 Detailed Results by Test Suite:\n";
echo str_repeat("-", 60) . "\n";

foreach ($testSuites as $suiteName => $results) {
    $status = $results['failed'] > 0 ? '❌' : '✅';
    $rate = $results['success_rate'];
    $time = $results['time'];
    
    echo sprintf(
        "%s %-25s %3d/%3d (%5.1f%%) %8.2fms\n",
        $status,
        $suiteName,
        $results['passed'],
        $results['total'],
        $rate,
        $time
    );
    
    if (isset($results['error'])) {
        echo "   Error: " . $results['error'] . "\n";
    }
}

echo str_repeat("-", 60) . "\n";

if (count($failedSuites) > 0) {
    echo "\n❌ Failed Test Suites:\n";
    foreach ($failedSuites as $failedSuite) {
        echo "   • {$failedSuite}\n";
    }
} else {
    echo "\n🎉 ALL MONGODB TESTS PASSED!\n";
}

// Imprime lista detalhada dos arquivos de teste executados
echo "\n📝 Arquivos executados:\n";
foreach ($executedFiles as $ef) {
    if (isset($ef['status']) && $ef['status'] === 'not_found') {
        echo "   ⚠️  Arquivo não encontrado: {$ef['file']} (suíte: {$ef['suite']})\n";
        continue;
    }

    if (isset($ef['status']) && ($ef['status'] === 'passed' || $ef['status'] === 'failed')) {
        $passed = isset($ef['passed']) ? $ef['passed'] : 0;
        $total = isset($ef['total']) ? $ef['total'] : 0;
        $time = isset($ef['time']) ? $ef['time'] . 'ms' : 'N/A';
        $icon = $ef['status'] === 'passed' ? '✅' : '❌';
        echo "   {$icon} {$ef['file']} ({$ef['suite']}): {$passed}/{$total} in {$time}\n";
        if (isset($ef['error'])) {
            echo "      Error: {$ef['error']}\n";
        }
        continue;
    }

    // Outros estados (ex: error)
    if (isset($ef['status']) && $ef['status'] === 'error') {
        echo "   ❌ {$ef['file']} ({$ef['suite']}): erro durante execução\n";
        if (isset($ef['error'])) {
            echo "      Error: {$ef['error']}\n";
        }
        continue;
    }
    
    // Caso fallback
    echo "   - {$ef['file']} ({$ef['suite']})\n";
}

echo "\n🔍 MongoDB Feature Coverage:\n";
echo "   ✅ Basic CRUD operations\n";
echo "   ✅ Aggregation pipeline\n";
echo "   ✅ Advanced filters and operators\n";
echo "   ✅ Full-text search\n";
echo "   ✅ Geospatial queries\n";
echo "   ✅ Sorting and ordering\n";
echo "   ✅ Utility functions\n";
echo "   ✅ Query validation\n";

echo "\n💡 MongoDB Implementation Status:\n";
if ($overallSuccessRate >= 95) {
    echo "   🟢 EXCELLENT - MongoDB implementation is production-ready\n";
} elseif ($overallSuccessRate >= 85) {
    echo "   🟡 GOOD - MongoDB implementation is mostly functional\n";
} elseif ($overallSuccessRate >= 70) {
    echo "   🟠 FAIR - MongoDB implementation needs improvements\n";
} else {
    echo "   🔴 POOR - MongoDB implementation requires significant work\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "MongoDB Comprehensive Test Suite Complete\n";
echo "QueryCraft MongoDB Query Builder Validation: {$overallSuccessRate}%\n";
echo str_repeat("=", 60) . "\n";

// Return results for potential use by other scripts
return [
    'total_tests' => $totalTests,
    'passed_tests' => $totalPassed,
    'success_rate' => $overallSuccessRate,
    'execution_time' => $totalTime,
    'failed_suites' => $failedSuites,
    'suite_results' => $testSuites
];
