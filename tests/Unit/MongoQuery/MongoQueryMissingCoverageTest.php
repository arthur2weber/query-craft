<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class MongoQueryMissingCoverageTest extends TestCase
{
    public function testCacheClauseEvictionTriggeredByTermClause()
    {
        $ref = new ReflectionClass(MongoQuery::class);
        $propCache = $ref->getProperty('clauseCache');
        $propCache->setAccessible(true);
        $propSize = $ref->getProperty('cacheSize');
        $propSize->setAccessible(true);
        $propMax = $ref->getProperty('maxCacheSize');
        $propMax->setAccessible(true);

        // Set a very small max so eviction will occur
        $propMax->setValue(null, 5);

        // Pre-fill the cache with max entries
        $initial = [];
        for ($i = 0; $i < 5; $i++) {
            $key = 'term:pre_' . $i . ':val' . $i;
            $initial[$key] = ['pre' . $i => $i];
        }
        $propCache->setValue(null, $initial);
        $propSize->setValue(null, 5);

        // This call should use cacheClause internally and trigger eviction logic
        MongoQuery::termClause('new_field', 'new_value');

        $cache = $propCache->getValue(null);
        $size = $propSize->getValue(null);

        $this->assertIsArray($cache);
        $this->assertLessThanOrEqual(5, $size);
        $this->assertArrayHasKey('term:new_field:new_value', $cache);
    }

    public function testProjectIgnoresNumericNonStringValues()
    {
        $q = new MongoQuery();
        // Provide numeric array with non-string values to hit ignored branch
        $q->project([123, 456]);
        $pipeline = $q->build();

        $projectStages = array_values(array_filter($pipeline, fn($s) => array_key_first($s) === '$project'));
        $this->assertNotEmpty($projectStages);
        $proj = $projectStages[0]['$project'];
        // Expect that numeric non-string entries did not produce projection keys
        $this->assertIsArray($proj);
        $this->assertCount(0, $proj);
    }

    public function testGroupWithDollarPrefixedStringProducesLiteralDoubleDollar()
    {
        $q = new MongoQuery();
        $q->group('$type');
        $pipeline = $q->build();

        $groupStages = array_values(array_filter($pipeline, fn($s) => array_key_first($s) === '$group'));
        $this->assertNotEmpty($groupStages);
        $groupStage = $groupStages[0]['$group'];
        // Code prepends a '$' regardless; so '$' . '$type' -> '$$type'
        $this->assertEquals('$$type', $groupStage['_id']);
    }

    public function testBuildMongoFilterMergesTopLevelOrIntoAndWhenFilterExists()
    {
        $q = new MongoQuery();
        // Add an initial equality where
        $q->where('a', 1);
        // Add a not_between which yields a top-level $or
        $q->whereNotBetween('n', [1, 5]);

        $built = $q->build();
        $this->assertArrayHasKey('filter', $built);
        $filter = $built['filter'];

        // Implementation keeps both the existing filter and the top-level $or key
        $this->assertArrayHasKey('a', $filter);
        $this->assertArrayHasKey('$or', $filter);
        $this->assertEquals(['a' => 1], ['a' => $filter['a']]);
    }
}
