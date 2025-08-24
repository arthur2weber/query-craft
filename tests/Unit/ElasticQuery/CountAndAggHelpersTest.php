<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryCountAndAggHelpersTest extends TestCase
{
    public function testCountReturnsInteger()
    {
        $q = new ElasticQuery();
        $this->assertIsInt($q->count());
        $this->assertEquals(0, $q->count());
    }

    public function testAvgCreatesAggregation()
    {
        $q = new ElasticQuery();
        $q->avg('price');
        $built = $q->build();

        $this->assertArrayHasKey('aggs', $built);
        $this->assertArrayHasKey('avg_price', $built['aggs']);
        $this->assertArrayHasKey('avg', $built['aggs']['avg_price']);
        $this->assertEquals(['field' => 'price'], $built['aggs']['avg_price']['avg']);
    }

    public function testMaxMinSumCreateAggregations()
    {
        $q = new ElasticQuery();
        $q->max('score')->min('score')->sum('score');
        $built = $q->build();

        $this->assertArrayHasKey('aggs', $built);
        $this->assertArrayHasKey('max_score', $built['aggs']);
        $this->assertArrayHasKey('min_score', $built['aggs']);
        $this->assertArrayHasKey('sum_score', $built['aggs']);
    }
}
