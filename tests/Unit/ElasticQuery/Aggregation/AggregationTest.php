<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryAggregationTest extends TestCase
{
    public function testAggregationAddsAgg()
    {
        $q = new ElasticQuery();
        $q->aggregation('by_status', ['terms' => ['field' => 'status']]);
        $built = $q->build();
        $this->assertArrayHasKey('aggs', $built);
        $this->assertArrayHasKey('by_status', $built['aggs']);
    }

    public function testAvgSumMinMaxCreateAggs()
    {
        $q = new ElasticQuery();
        $q->avg('price')->sum('price')->min('price')->max('price');
        $built = $q->build();
        $this->assertArrayHasKey('aggs', $built);
    }
}
