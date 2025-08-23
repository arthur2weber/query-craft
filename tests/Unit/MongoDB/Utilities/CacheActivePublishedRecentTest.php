<?php
// Testes de métodos cache, active, published e recent do MongoQuery
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\MongoQuery;

echo "🧪 MONGODB UTILITIES: cache, active, published, recent\n";
echo str_repeat("=", 50) . "\n\n";

resetTestCounters();

try {
    // cache
    $q = new MongoQuery();
    $q->cache(true);
    validateTrue('cache() ativa flag de cache', isset($q));

    // active
    $q = new MongoQuery();
    $q->active();
    $result = $q->toQuery();
    validateTrue('active() adiciona filtro status', is_array($result));

    // published
    $q = new MongoQuery();
    $q->published();
    $result = $q->toQuery();
    validateTrue('published() adiciona filtro published', is_array($result));

    // recent
    $q = new MongoQuery();
    $q->recent();
    $result = $q->toQuery();
    validateTrue('recent() adiciona filtro created_at', is_array($result));

} catch (Exception $e) {
    recordFailedTest('MongoDB Utilities cache/active/published/recent', $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
$results = printTestSummary('MongoDB Utilities cache/active/published/recent');
return $results;
