<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryUtilityTest extends TestCase
{
    public function testClauseCachePopulation()
    {
        ElasticQuery::matchClause('title','abc');
        $r = new ReflectionClass(ElasticQuery::class);
        $p = $r->getProperty('clauseCache');
        $p->setAccessible(true);
        $cache = $p->getValue();
        $this->assertNotEmpty($cache);
    }

    public function testToDSLProducesJsonString()
    {
        $q = new ElasticQuery();
        $json = $q->toDSL();
        $this->assertIsString($json);
        $this->assertStringContainsString('"query"', $json);
    }

    public function testHighlightBuildsStructure()
    {
        $q = new ElasticQuery();
        $q->highlight(['title','body'], '<b>','</b>');
        $built = $q->build();
        $this->assertArrayHasKey('highlight', $built);
        $this->assertArrayHasKey('fields', $built['highlight']);
        $this->assertIsObject($built['highlight']['fields']['title']);
    }

    public function testTimeoutValidation()
    {
        $q = new ElasticQuery();
        $this->expectException(InvalidArgumentException::class);
        $q->timeout('500');
    }
}
