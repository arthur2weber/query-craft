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
        'tests/Unit/Elasticsearch/Core/BasicQueryTest.php',
        'tests/Unit/Elasticsearch/Search/TextSearchTest.php'
    ],
    'core' => [
        'tests/Unit/Elasticsearch/Core/BasicQueryTest.php',
        'tests/Unit/Elasticsearch/Core/BooleanQueryTest.php',
        'tests/Unit/Elasticsearch/Core/NestedQueryTest.php',
        'tests/Unit/Elasticsearch/Core/StaticClauseTest.php',
        'tests/Unit/Elasticsearch/Core/DeveloperExperienceTest.php'
    ],
    'search' => [
        'tests/Unit/Elasticsearch/Search/TextSearchTest.php',
        'tests/Unit/Elasticsearch/Search/FuzzyWildcardTest.php'
    ],
    'filters' => [
        'tests/Unit/Elasticsearch/Filters/RangeTermTest.php',
        'tests/Unit/Elasticsearch/Filters/WhereClauseTest.php'
    ],
    'utilities' => [
        'tests/Unit/Elasticsearch/Utilities/UtilityMethodsTest.php'
    ],
    'aggregations' => [
        'tests/Unit/Elasticsearch/Aggregations/AggregationTest.php'
    ],
    'geographic' => [
        'tests/Unit/Elasticsearch/Geographic/GeographicTest.php'
    ],
    'sorting' => [
        'tests/Unit/Elasticsearch/Sorting/SortingTest.php'
    ],
    'validation' => [
        'tests/Unit/Elasticsearch/Validation/ComprehensiveValidationTest.php'
    ],
    'dx' => [
        'tests/Unit/Elasticsearch/Core/DeveloperExperienceTest.php'
    ],
    'multibackend' => [
        'tests/Unit/Query/MultiBackendTest.php'
    ],
    'elastic' => [
        // Todos os testes de Elasticsearch (todas as categorias já existentes)
        'tests/Unit/Elasticsearch/Core/BasicQueryTest.php',
        'tests/Unit/Elasticsearch/Core/BooleanQueryTest.php',
        'tests/Unit/Elasticsearch/Core/NestedQueryTest.php',
        'tests/Unit/Elasticsearch/Core/StaticClauseTest.php',
        'tests/Unit/Elasticsearch/Core/DeveloperExperienceTest.php',
        'tests/Unit/Elasticsearch/Search/TextSearchTest.php',
        'tests/Unit/Elasticsearch/Search/FuzzyWildcardTest.php',
        'tests/Unit/Elasticsearch/Filters/RangeTermTest.php',
        'tests/Unit/Elasticsearch/Filters/WhereClauseTest.php',
        'tests/Unit/Elasticsearch/Utilities/UtilityMethodsTest.php',
        'tests/Unit/Elasticsearch/Aggregations/AggregationTest.php',
        'tests/Unit/Elasticsearch/Geographic/GeographicTest.php',
        'tests/Unit/Elasticsearch/Sorting/SortingTest.php',
        'tests/Unit/Elasticsearch/Validation/ComprehensiveValidationTest.php',
        'tests/Unit/Query/MultiBackendTest.php'
    ],
    'mongo' => [
        // Expande para cada suíte/categoria MongoDB para que 'all' mostre um check por categoria
        'tests/Unit/MongoDB/Core/BasicQueryTest.php',
        'tests/Unit/MongoDB/Aggregations/AggregationTest.php',
        'tests/Unit/MongoDB/Filters/SimpleAdvancedFiltersTest.php',
        'tests/Unit/MongoDB/Search/TextSearchTest.php',
        'tests/Unit/MongoDB/Geographic/GeographicTest.php',
        'tests/Unit/MongoDB/Sorting/SortingTest.php',
        'tests/Unit/MongoDB/Utilities/UtilitiesTest.php',
        'tests/Unit/MongoDB/Validation/ValidationTest.php'
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

if ($cat === 'mongo') {
    // Executa o runner único do MongoDB
    require 'tests/Unit/MongoDB/RunAllMongoDBTests.php';
    exit(0);
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
    // Print a header separator once when we first encounter each backend group
    static $printedElasticHeader = false;
    static $printedMongoHeader = false;

    // Detect backend based on path
    if (strpos($file, 'MongoDB') !== false) {
        $backend = 'mongo';
    } elseif (strpos($file, 'Elasticsearch') !== false) {
        $backend = 'elastic';
    } else {
        $backend = 'other';
    }

    // Print header for Elasticsearch tests once
    if ($backend === 'elastic' && !$printedElasticHeader) {
        echo "\n" . str_repeat("=", 40) . "\n";
        echo "🔁 Running Elasticsearch tests" . "\n";
        echo str_repeat("=", 40) . "\n\n";
        $printedElasticHeader = true;
    }

    // Print header for MongoDB tests once
    if ($backend === 'mongo' && !$printedMongoHeader) {
        echo "\n" . str_repeat("=", 40) . "\n";
        echo "🔁 Running MongoDB tests" . "\n";
        echo str_repeat("=", 40) . "\n\n";
        $printedMongoHeader = true;
    }

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
