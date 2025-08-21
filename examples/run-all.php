<?php
/**
 * Run All Examples Script
 * 
 * This script executes all example files in the correct order to demonstrate
 * the full capabilities of the QueryCraft DSL Builder.
 * 
 * @package Arthur2weber\QueryCraft
 * @author Arthur
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "================================================================================\n";
echo "🚀 QUERYCRAFT - RUNNING ALL EXAMPLES\n";
echo "================================================================================\n\n";

$examples = [
    'blog-search.php' => 'Blog Content Search Examples',
    'ecommerce-search.php' => 'E-commerce Product Search Examples',
    'geographic-search.php' => 'Geographic and Location-based Search Examples',
    'analytics-aggregations.php' => 'Analytics and Aggregation Examples',
    'comprehensive-examples.php' => 'Comprehensive Feature Demonstration'
];

$totalExamples = count($examples);
$currentExample = 0;

foreach ($examples as $filename => $description) {
    $currentExample++;
    $filepath = __DIR__ . '/' . $filename;
    
    echo "📊 [{$currentExample}/{$totalExamples}] Running: {$description}\n";
    echo "📁 File: {$filename}\n";
    echo str_repeat("-", 80) . "\n";
    
    if (file_exists($filepath)) {
        try {
            // Capture output to prevent interference between examples
            ob_start();
            include $filepath;
            $output = ob_get_clean();
            
            // Display the output
            echo $output;
            
            echo "\n✅ Example completed successfully!\n";
            
        } catch (Exception $e) {
            echo "❌ Error running example: " . $e->getMessage() . "\n";
        } catch (Error $e) {
            echo "❌ Fatal error in example: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Example file not found: {$filepath}\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n\n";
    
    // Add a small delay between examples for readability
    if ($currentExample < $totalExamples) {
        echo "⏳ Preparing next example...\n\n";
        sleep(1);
    }
}

echo "🎉 ALL EXAMPLES COMPLETED!\n";
echo "================================================================================\n";
echo "Summary:\n";
echo "  • Total examples executed: {$totalExamples}\n";
echo "  • Coverage: Basic searches, advanced queries, aggregations, geo-search\n";
echo "  • Use cases: Blog, E-commerce, Analytics, Geographic applications\n";
echo "\n💡 Tips for using this library:\n";
echo "  1. Start with basic examples (blog-search.php)\n";
echo "  2. Explore specific use cases relevant to your application\n";
echo "  3. Combine multiple query types for complex searches\n";
echo "  4. Use aggregations for analytics and reporting\n";
echo "  5. Optimize performance with proper filtering\n";
echo "\n📚 Documentation:\n";
echo "  • README.md - Complete library documentation\n";
echo "  • ROADMAP.md - Future development plans\n";
echo "  • Source code in src/ - Well-documented with PHPDoc\n";
echo "================================================================================\n";
