<?php
/**
 * Developer Experience Features Example
 * 
 * This example demonstrates all the Developer Experience features
 * available in QueryCraft including verbose mode, performance warnings,
 * and smart error translation.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Arthur2weber\QueryCraft\ElasticQuery;

echo "🔧 QueryCraft - Developer Experience Features Demo\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// 1. Verbose Mode Example
echo "1. 🔍 VERBOSE MODE EXAMPLE\n";
echo "-" . str_repeat("-", 30) . "\n";

$query = new ElasticQuery();
$query->verbose(); // Enable verbose mode

$result = $query
    ->match('title', 'PHP Elasticsearch Tutorial')
    ->filter('status', 'published')
    ->range('created_at', 'gte', '2024-01-01')
    ->sort('created_at', 'desc')
    ->size(10)
    ->build();

echo "Query built successfully! Here's what happened:\n\n";

// Display verbose logs
foreach ($query->getVerboseLog() as $log) {
    echo "📝 " . $log . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n\n";

// 2. Performance Warnings Example
echo "2. ⚠️ PERFORMANCE WARNINGS EXAMPLE\n";
echo "-" . str_repeat("-", 35) . "\n";

$warningQuery = new ElasticQuery();
$warningQuery->verbose();

// Build a query that will trigger performance warnings
$warningQuery
    ->match('content', 'Laravel')
    ->wildcard('title', '*tutorial*')      // Will trigger wildcard warning
    ->size(50000)                         // Will trigger large result warning
    ->from(8000)                          // Will trigger deep pagination warning
    ->regexp('description', '.*beginner.*'); // Will trigger regex warning

// Get performance warnings
$performanceWarnings = $warningQuery->getPerformanceWarnings();
if (!empty($performanceWarnings)) {
    echo "Performance warnings detected:\n\n";
    foreach ($performanceWarnings as $warning) {
        echo "⚠️ " . $warning . "\n";
    }
} else {
    echo "No performance warnings detected.\n";
}

echo "\n" . str_repeat("=", 60) . "\n\n";

// 3. Error Translation Example
echo "3. 🚨 ERROR TRANSLATION EXAMPLE\n";
echo "-" . str_repeat("-", 32) . "\n";

$errorQuery = new ElasticQuery();

// Example Elasticsearch errors that might occur
$sampleErrors = [
    'parsing_exception: [match] query does not support [invalid_parameter]',
    'search_phase_execution_exception.*timeout',
    'index_not_found_exception: no such index [missing_products]',
    'mapper_parsing_exception: failed to parse field [created_at]',
    'query_shard_exception: Failed to parse query',
    'Request timeout after 30000ms',
    'security_exception: action [indices:data/read/search] is unauthorized',
];

echo "Translating common Elasticsearch errors to human-readable messages:\n\n";

foreach ($sampleErrors as $i => $error) {
    $translated = $errorQuery->translateError($error);
    
    echo "Error " . ($i + 1) . ":\n";
    echo "🔴 Original: " . $error . "\n";
    echo "✅ Translated: " . $translated . "\n\n";
}

echo str_repeat("=", 60) . "\n\n";

// 4. Complete Workflow Example
echo "4. 🎯 COMPLETE DX WORKFLOW EXAMPLE\n";
echo "-" . str_repeat("-", 35) . "\n";

function buildSearchQueryWithDX($searchTerm, $category = null, $enableDebugging = true) {
    $query = new ElasticQuery();
    
    if ($enableDebugging) {
        $query->verbose();
    }
    
    try {
        // Build the search query
        $result = $query
            ->match('title', $searchTerm)
            ->should([
                'match' => [
                    'content' => [
                        'query' => $searchTerm,
                        'boost' => 0.8
                    ]
                ]
            ])
            ->filter('status', 'published');
        
        if ($category) {
            $result->filter('category', $category);
        }
        
        $result
            ->sort('_score', 'desc')
            ->sort('created_at', 'desc')
            ->size(20);
        
        $queryArray = $result->build();
        
        // Show DX information if debugging is enabled
        if ($enableDebugging) {
            echo "✅ Query built successfully for term: '$searchTerm'\n\n";
            
            // Show verbose logs
            echo "📋 Build steps:\n";
            foreach ($query->getVerboseLog() as $log) {
                echo "  " . $log . "\n";
            }
            
            // Check for warnings
            $warnings = $query->getWarnings();
            if (!empty($warnings)) {
                echo "\n⚠️ Warnings:\n";
                foreach ($warnings as $warning) {
                    echo "  " . $warning . "\n";
                }
            } else {
                echo "\n✅ No warnings detected.\n";
            }
            
            // Show query size
            echo "\n📊 Query info:\n";
            echo "  Size: " . strlen(json_encode($queryArray)) . " bytes\n";
            echo "  Expected results: up to 20 documents\n";
        }
        
        return $queryArray;
        
    } catch (Exception $e) {
        if ($enableDebugging) {
            $humanError = $query->translateError($e->getMessage());
            echo "🚨 Error occurred:\n";
            echo "  Original: " . $e->getMessage() . "\n";
            echo "  Translated: " . $humanError . "\n";
        }
        throw $e;
    }
}

// Example usage
echo "Building search query with full DX features enabled:\n\n";
$searchQuery = buildSearchQueryWithDX('PHP Laravel Tutorial', 'programming');

echo "\n" . str_repeat("=", 60) . "\n\n";

// 5. Best Practices Example
echo "5. 💡 BEST PRACTICES EXAMPLE\n";
echo "-" . str_repeat("-", 28) . "\n";

echo "Here are some best practices for using DX features:\n\n";

echo "✅ DO:\n";
echo "  • Enable verbose mode during development\n";
echo "  • Check performance warnings regularly\n";
echo "  • Use error translation in exception handling\n";
echo "  • Log both original and translated errors\n";
echo "  • Monitor warnings in staging environments\n\n";

echo "❌ DON'T:\n";
echo "  • Leave verbose mode enabled in production\n";
echo "  • Ignore performance warnings\n";
echo "  • Assume all errors are user-friendly\n";
echo "  • Skip logging for debugging purposes\n\n";

// Example of production-ready code
echo "Example production-ready code:\n\n";
echo "```php\n";
echo "function productionSearch(\$term) {\n";
echo "    \$query = new ElasticQuery();\n";
echo "    \n";
echo "    // Enable verbose only in development\n";
echo "    if (getenv('APP_ENV') === 'development') {\n";
echo "        \$query->verbose();\n";
echo "    }\n";
echo "    \n";
echo "    try {\n";
echo "        \$result = \$query->match('title', \$term)->build();\n";
echo "        \n";
echo "        // Log warnings in development\n";
echo "        if (getenv('APP_ENV') === 'development') {\n";
echo "            \$warnings = \$query->getPerformanceWarnings();\n";
echo "            foreach (\$warnings as \$warning) {\n";
echo "                error_log('Performance Warning: ' . \$warning);\n";
echo "            }\n";
echo "        }\n";
echo "        \n";
echo "        return \$result;\n";
echo "        \n";
echo "    } catch (Exception \$e) {\n";
echo "        \$humanError = \$query->translateError(\$e->getMessage());\n";
echo "        error_log('Search Error: ' . \$humanError);\n";
echo "        throw new Exception(\$humanError);\n";
echo "    }\n";
echo "}\n";
echo "```\n\n";

echo "🎉 Developer Experience Features Demo Complete!\n";
echo "Check the docs/developer-experience.md file for more detailed information.\n";
