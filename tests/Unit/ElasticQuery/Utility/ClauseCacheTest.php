<?php

namespace Tests\Unit\ElasticQuery;

require_once __DIR__ . '/TestableElasticQuery.php';

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery as ElasticQueryClass;

class ClauseCacheTest extends TestCase
{
    public function testCacheEvictionOnMaxSize()
    {
        $q = new TestableElasticQuery();

        $ref = new \ReflectionClass(ElasticQueryClass::class);
        $propCache = $ref->getProperty('clauseCache');
        $propCache->setAccessible(true);
        $propSize = $ref->getProperty('cacheSize');
        $propSize->setAccessible(true);
        $propMax = $ref->getProperty('maxCacheSize');
        $propMax->setAccessible(true);

        // set a tiny max size to trigger eviction path
        $propCache->setValue(null, []);
        $propSize->setValue(null, 0);
        $propMax->setValue(null, 3);

        // populate up to max
        TestableElasticQuery::matchClause('f', 'v1');
        TestableElasticQuery::matchClause('f', 'v2');
        TestableElasticQuery::matchClause('f', 'v3');

        $this->assertEquals(3, $propSize->getValue(null));

        // adding a 4th should trigger eviction logic which removes the old keys and then adds the new one
        TestableElasticQuery::matchClause('f', 'v4');

        $cache = $propCache->getValue(null);
        $size = $propSize->getValue(null);

        $this->assertEquals(1, $size, 'Cache size should have been reduced to 1 after eviction and add');
        $this->assertArrayHasKey('match:f:v4', $cache);
        $this->assertCount(1, $cache);
    }
}
