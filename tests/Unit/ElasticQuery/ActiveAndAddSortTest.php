<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryActiveAndAddSortTest extends TestCase
{
    public function testActiveAddsStatusFilter()
    {
        $q = new ElasticQuery();
        $q->active();
        $built = $q->build();

        $this->assertArrayHasKey('query', $built);
        $this->assertArrayHasKey('bool', $built['query']);
        $this->assertArrayHasKey('filter', $built['query']['bool']);
        $this->assertNotEmpty($built['query']['bool']['filter']);

        $firstFilter = $built['query']['bool']['filter'][0];
        $this->assertArrayHasKey('term', $firstFilter);
        $this->assertEquals(['status' => 'active'], $firstFilter['term']);
    }

    public function testAddSortAppendsCustomSort()
    {
        $q = new ElasticQuery();
        $q->addSort(['_score' => ['order' => 'desc']]);
        $built = $q->build();

        $this->assertArrayHasKey('sort', $built);
        $this->assertEquals([['_score' => ['order' => 'desc']]], $built['sort']);
    }
}
