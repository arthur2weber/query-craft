<?php
echo "🧪 Simple Test Running...\n";
echo "PHP is working!\n";

require_once __DIR__ . '/../../../../src/ElasticQueryInterface.php';
require_once __DIR__ . '/../../../../src/ElasticQuery.php';

use Arthur2weber\QueryCraft\ElasticQuery;

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
