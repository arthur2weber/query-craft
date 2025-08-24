<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryCoverageFinalTest extends TestCase
{
    public function testWhereNotEqualsCreatesMustNotTerm()
    {
        $q = new ElasticQuery();
        $q->where('age', '!=', 30);
        $built = $q->build();
        $this->assertArrayHasKey('must_not', $built['query']['bool']);
        $this->assertContainsEquals(['term' => ['age' => 30]], $built['query']['bool']['must_not']);
    }

    public function testWhereBetweenSuccessProvidesTwoFilters()
    {
        $q = new ElasticQuery();
        $q->whereBetween('score', [0, 100]);
        $built = $q->build();
        $this->assertArrayHasKey('filter', $built['query']['bool']);
        $this->assertCount(2, $built['query']['bool']['filter']);
        $this->assertEquals(['range' => ['score' => ['gte' => 0]]], $built['query']['bool']['filter'][0]);
        $this->assertEquals(['range' => ['score' => ['lte' => 100]]], $built['query']['bool']['filter'][1]);
    }
}
