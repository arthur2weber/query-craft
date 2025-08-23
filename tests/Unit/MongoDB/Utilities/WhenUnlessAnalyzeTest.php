<?php
// Testes de métodos when, unless e analyze do MongoQuery
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\MongoQuery;

echo "🧪 MONGODB UTILITIES: when, unless, analyze\n";
echo str_repeat("=", 50) . "\n\n";

resetTestCounters();

try {
    // when (true)
    $q = new MongoQuery();
    $q->when(true, function($q) { $q->where('flag', true); });
    $result = $q->toQuery();
    validateTrue('when() executa callback se true', is_array($result));

    // unless (false)
    $q = new MongoQuery();
    $q->unless(false, function($q) { $q->where('flag', false); });
    $result = $q->toQuery();
    validateTrue('unless() executa callback se false', is_array($result));

    // analyze
    $q = new MongoQuery();
    $q->analyze();
    validateTrue('analyze() marca como analisado', isset($q));

} catch (Exception $e) {
    recordFailedTest('MongoDB Utilities when/unless/analyze', $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
$results = printTestSummary('MongoDB Utilities when/unless/analyze');
return $results;
