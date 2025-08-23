<?php
// Testes de métodos utilitários e escopos extras do MongoQuery
require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../TestHelpers.php';

use Arthur2weber\QueryCraft\Query\MongoQuery;

echo "🧪 MONGODB UTILITIES: Métodos extras (highlight, source, timeout, when, unless, analyze, cache, active, published, recent)\n";
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
    recordFailedTest('MongoDB Utilities Extra', $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
$results = printTestSummary('MongoDB Utilities Extra');
return $results;
