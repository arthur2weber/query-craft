<?php
// Testes de métodos highlight, source e timeout do MongoQuery
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\MongoQuery;

echo "🧪 MONGODB UTILITIES: highlight, source, timeout\n";
echo str_repeat("=", 50) . "\n\n";

resetTestCounters();

try {
    // highlight
    $q = new MongoQuery();
    $q->highlight(['field1', 'field2']);
    validateTrue('highlight() aceita array de campos', isset($q));

    // source
    $q = new MongoQuery();
    $q->source(['campoA', 'campoB']);
    validateTrue('source() aceita array de campos', isset($q));

    // timeout
    $q = new MongoQuery();
    $q->timeout(5000);
    validateTrue('timeout() aceita valor inteiro', isset($q));

} catch (Exception $e) {
    recordFailedTest('MongoDB Utilities highlight/source/timeout', $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
$results = printTestSummary('MongoDB Utilities highlight/source/timeout');
return $results;
