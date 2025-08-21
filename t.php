<?php
/**
 * Teste Ultra-Rápido - Uma linha de comando
 * 
 * Uso: php t.php [categoria]
 */

$cat = isset($argv[1]) ? $argv[1] : 'quick';

// Configuração dos testes
$testDefinitions = [
    'quick' => [
        'tests/Unit/Core/BasicQueryTest.php',
        'tests/Unit/Search/TextSearchTest.php'
    ],
    'core' => [
        'tests/Unit/Core/BasicQueryTest.php',
        'tests/Unit/Core/BooleanQueryTest.php',
        'tests/Unit/Core/NestedQueryTest.php',
        'tests/Unit/Core/StaticClauseTest.php',
        'tests/Unit/Core/DeveloperExperienceTest.php'
    ],
    'search' => [
        'tests/Unit/Search/TextSearchTest.php',
        'tests/Unit/Search/FuzzyWildcardTest.php'
    ],
    'filters' => [
        'tests/Unit/Filters/RangeTermTest.php',
        'tests/Unit/Filters/WhereClauseTest.php'
    ],
    'utilities' => [
        'tests/Unit/Utilities/UtilityMethodsTest.php'
    ],
    'aggregations' => [
        'tests/Unit/Aggregations/AggregationTest.php'
    ],
    'geographic' => [
        'tests/Unit/Geographic/GeographicTest.php'
    ],
    'sorting' => [
        'tests/Unit/Sorting/SortingTest.php'
    ],
    'validation' => [
        'tests/Unit/Validation/ComprehensiveValidationTest.php'
    ],
    'dx' => [
        'tests/Unit/Core/DeveloperExperienceTest.php'
    ]
];

// Para 'all', combina todos os testes
if ($cat === 'all') {
    $testDefinitions['all'] = [];
    foreach ($testDefinitions as $key => $files) {
        if ($key !== 'all') {
            $testDefinitions['all'] = array_merge($testDefinitions['all'], $files);
        }
    }
    $testDefinitions['all'] = array_unique($testDefinitions['all']);
}

if (!isset($testDefinitions[$cat])) {
    echo "❌ Categoria desconhecida: $cat\n";
    echo "📋 Use: php t.php [" . implode('|', array_keys($testDefinitions)) . "|all]\n";
    exit(1);
}

echo "🧪 QueryCraft ($cat)\n" . str_repeat("=", 20) . "\n";

$passed = $total = 0;
$failed_tests = [];
$start = microtime(true);

foreach ($testDefinitions[$cat] as $file) {
    if (!file_exists($file)) {
        echo "⚠️  Arquivo não encontrado: $file\n";
        continue;
    }
    
    $testName = basename($file, '.php');
    echo "$testName: ";
    
    // Executa em processo separado para evitar conflitos
    $test_start = microtime(true);
    $output = shell_exec("php \"$file\" 2>/dev/null");
    $test_time = round((microtime(true) - $test_start) * 1000);
    
    if ($output !== null) {
        $p = substr_count($output, '✅');
        $f = substr_count($output, '❌');
        $t = $p + $f;
        
        if ($t > 0) {
            $rate = round(($p / $t) * 100, 1);
            echo ($p == $t ? "✅" : "❌") . " $p/$t ({$rate}%) [{$test_time}ms]\n";
            $passed += $p;
            $total += $t;
            
            if ($p < $t) {
                $failed_tests[] = $testName;
            }
        } else {
            echo "⚠️  Sem testes detectados\n";
        }
    } else {
        echo "💥 ERRO na execução\n";
        $failed_tests[] = $testName . " (Erro)";
    }
}

$time = round((microtime(true) - $start) * 1000);
$rate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

echo "\n";
echo "🎯 RESUMO: $passed/$total ({$rate}%) em {$time}ms\n";

if ($rate >= 95) {
    echo "🎉 PERFEITO!\n";
} elseif ($rate >= 80) {
    echo "👍 BOM!\n";
} else {
    echo "⚠️  ATENÇÃO!\n";
}

if (!empty($failed_tests)) {
    echo "❌ Falharam: " . implode(', ', $failed_tests) . "\n";
}

echo "\n";
