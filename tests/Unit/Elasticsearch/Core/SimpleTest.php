<?php
echo "🧪 Simple Test Running...\n";
echo "PHP is working!\n";

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\ElasticQuery;

echo "🧪 CORE: Simple Query Tests\n";
echo str_repeat("=", 40) . "\n\n";

resetTestCounters();

$query = new ElasticQuery();
echo "✅ ElasticQuery created successfully\n";

// Test verbose mode
$query->verbose(true);
echo "✅ Verbose mode enabled\n";

$query->size(10);
$verboseLog = $query->getVerboseLog();
if (!empty($verboseLog)) {
    echo "✅ Verbose logging works\n";
} else {
    echo "❌ Verbose logging failed\n";
}

echo "🎉 Simple test completed!\n";
