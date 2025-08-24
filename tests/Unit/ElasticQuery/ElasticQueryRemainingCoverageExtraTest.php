<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryRemainingCoverageExtraTest extends TestCase
{
    public function testShouldSetsMinimumShouldMatch()
    {
        $q = new ElasticQuery();
        $q->should(['term' => ['a' => 'b']], 2);
        $built = $q->build();
        $this->assertArrayHasKey('minimum_should_match', $built['query']['bool']);
        $this->assertEquals(2, $built['query']['bool']['minimum_should_match']);
    }

    public function testWhereUnsupportedOperatorThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $q = new ElasticQuery();
        $q->where('age', 'LIKE', 30);
    }

    public function testWhereBetweenCountNotTwoThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $q = new ElasticQuery();
        $q->whereBetween('f', [1]);
    }

    public function testWhereBetweenMinGreaterThanMaxThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $q = new ElasticQuery();
        $q->whereBetween('f', [5, 1]);
    }

    public function testWhereBetweenSuccessAddsTwoFilters()
    {
        $q = new ElasticQuery();
        $q->whereBetween('f', [1, 5]);
        $built = $q->build();
        $this->assertArrayHasKey('filter', $built['query']['bool']);
        $this->assertCount(2, $built['query']['bool']['filter']);
    }

    public function testPaginateInvalidPageThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $q = new ElasticQuery();
        $q->paginate(10, 0);
    }

    public function testPaginateInvalidPerPageThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $q = new ElasticQuery();
        $q->paginate(0, 1);
    }

    public function testPaginateSuccessReturnsPaginationArray()
    {
        $q = new ElasticQuery();
        $result = $q->paginate(2, 2);
        $this->assertEquals(2, $result['per_page']);
        $this->assertEquals(2, $result['current_page']);
        $this->assertEquals(3, $result['from']); // (page-1)*perPage + 1 -> (2-1)*2 +1 =3
        $this->assertEquals(4, $result['to']);
    }
}
