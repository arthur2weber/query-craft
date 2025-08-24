<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class WhereErrorsTest extends TestCase
{
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
}
