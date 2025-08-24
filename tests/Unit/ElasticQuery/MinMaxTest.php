<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class MinMaxTest extends TestCase
{
    public function testMinAndMaxCreateAggregations()
    {
        $q = new ElasticQuery();
        $q->min('price');
        $q->max('price');

        $built = $q->toQuery();

        $this->assertIsArray($built);
        $this->assertStringContainsString('"aggs"', json_encode($built));
        $this->assertStringContainsString('min', json_encode($built));
        $this->assertStringContainsString('max', json_encode($built));
    }
}
