<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class LimitOffsetTest extends TestCase
{
    public function testLimitSetsSizeAndFromWhenBuilding()
    {
        $q = new ElasticQuery();
        $q->limit(10)->offset(5);

        $built = $q->build();

        $this->assertIsArray($built);
        $this->assertSame(10, $built['size'] ?? null);
        $this->assertSame(5, $built['from'] ?? null);
    }

    public function testOffsetAliasSkipAndTakeAliasWork()
    {
        $q = new ElasticQuery();
        $q->take(3)->skip(2);

        $built = $q->toQuery();

        $this->assertIsArray($built);
        $this->assertSame(3, $built['size'] ?? null);
        $this->assertSame(2, $built['from'] ?? null);
    }
}
