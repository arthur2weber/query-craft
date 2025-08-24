<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class WhereBetweenAndPaginateTest extends TestCase
{
    public function testWhereBetweenSuccessAddsTwoFilters()
    {
        $q = new ElasticQuery();
        $q->whereBetween('f', [1, 5]);
        $built = $q->build();
        $this->assertArrayHasKey('filter', $built['query']['bool']);
        $this->assertCount(2, $built['query']['bool']['filter']);
    }

    public function testPaginateSuccessReturnsPaginationArray()
    {
        $q = new ElasticQuery();
        $result = $q->paginate(2, 2);
        $this->assertEquals(2, $result['per_page']);
        $this->assertEquals(2, $result['current_page']);
        $this->assertEquals(3, $result['from']);
        $this->assertEquals(4, $result['to']);
    }
}
