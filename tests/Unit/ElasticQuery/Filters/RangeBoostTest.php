<?php

namespace Tests\Unit\ElasticQuery;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../TestableElasticQuery.php';

class RangeBoostTest extends TestCase
{
    private function makeTestable()
    {
        return new \Tests\Unit\ElasticQuery\TestableElasticQuery();
    }

    public function testRangeBoostValidAndInvalidOperator()
    {
        $q = $this->makeTestable();

        $this->expectException(\InvalidArgumentException::class);
        $q->rangeBoost('price', 'badop', 10, 1.2);

        // valid operator
        $res = $q->rangeBoost('price', 'gte', 50, 1.5);
        $this->assertInstanceOf(\Arthur2weber\QueryCraft\Query\ElasticQuery::class, $res);
        $built = $q->build();
        $this->assertArrayHasKey('query', $built);
    }
}
