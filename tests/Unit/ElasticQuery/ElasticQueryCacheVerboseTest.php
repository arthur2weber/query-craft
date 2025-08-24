<?php

namespace Tests\Unit\ElasticQuery;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TestableElasticQuery.php';

class ElasticQueryCacheVerboseTest extends TestCase
{
    private function makeTestable()
    {
        return new TestableElasticQuery();
    }

    public function testVerboseModeLogsAndClears()
    {
        $q = $this->makeTestable();
        $q->verbose(true);
        // verbose should record messages
        $q->match('title', 'x');
        $logs = $q->getVerboseLog();
        $this->assertNotEmpty($logs);

        // disabling verbose clears logs and warnings
        $q->verbose(false);
        $this->assertEmpty($q->getVerboseLog());
        $this->assertEmpty($q->getWarnings());
    }

    public function testAddWarningAndPerformanceWarningMerge()
    {
        $q = $this->makeTestable();
        // add a logical warning via search empty string (uses addWarning)
        $q->search('', ['a']);
        // add performance warning via wildcard leading star
        $q->wildcard('f', '*term');

        $warnings = $q->getWarnings();
        $pws = $q->getPerformanceWarnings();

        $this->assertNotEmpty($warnings, 'Expected user warnings to be present');
        $this->assertNotEmpty($pws, 'Expected performance warnings to be present');
    }

    public function testFirstReturnsBuiltOrHit()
    {
        $q = $this->makeTestable();

        // by default build is non-empty so first returns something
        $first = $q->first();
        $this->assertNotNull($first);

        // set query to include hits.hits via reflection and ensure first returns that hit
        $ref = new \ReflectionObject($q);
        $prop = $ref->getProperty('query');
        $prop->setAccessible(true);
        $prop->setValue($q, ['hits' => ['hits' => [['doc' => 123]]]]);

        $firstHit = $q->first();
        $this->assertIsArray($firstHit);
        $this->assertArrayHasKey('doc', $firstHit);
    }

    public function testCacheClauseStoresAndEvicts()
    {
        $q = $this->makeTestable();

        $ref = new \ReflectionObject($q);
        $propCache = $ref->getProperty('clauseCache');
        $propCache->setAccessible(true);
        $propCache->setValue($q, []);

        $propSize = $ref->getProperty('cacheSize');
        $propSize->setAccessible(true);
        $propSize->setValue($q, 0);

        $propMax = $ref->getProperty('maxCacheSize');
        $propMax->setAccessible(true);
        $propMax->setValue($q, 3);

        // add four entries to trigger eviction logic
        \Arthur2weber\QueryCraft\Query\ElasticQuery::termClause('a', '1');
        \Arthur2weber\QueryCraft\Query\ElasticQuery::termClause('b', '2');
        \Arthur2weber\QueryCraft\Query\ElasticQuery::termClause('c', '3');
        \Arthur2weber\QueryCraft\Query\ElasticQuery::termClause('d', '4');

        $cache = $propCache->getValue();
        $size = $propSize->getValue();

        $this->assertLessThanOrEqual(3, count($cache));
        $this->assertEquals(count($cache), $size);
    }

    public function testMatchClauseArrayDoesNotCache()
    {
        $q = $this->makeTestable();
        $ref = new \ReflectionObject($q);
        $propCache = $ref->getProperty('clauseCache');
        $propCache->setAccessible(true);
        $propCache->setValue($q, []);

        \Arthur2weber\QueryCraft\Query\ElasticQuery::matchClause('f', ['query' => 'x', 'boost' => 1.2]);
        $cache = $propCache->getValue();
        // array-valued matchClause should not create a cached string key
        $this->assertEmpty($cache);
    }

    public function testToQueryReturnsBuild()
    {
        $q = $this->makeTestable();
        $q->match('a', 'b');
        $this->assertEquals($q->build(), $q->toQuery());
    }
}
