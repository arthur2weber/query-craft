<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ShouldClauseTest extends TestCase
{
    public function testShouldSetsMinimumShouldMatch()
    {
        $q = new ElasticQuery();
        $q->should(['term' => ['a' => 'b']], 2);
        $built = $q->build();
        $this->assertArrayHasKey('minimum_should_match', $built['query']['bool']);
        $this->assertEquals(2, $built['query']['bool']['minimum_should_match']);
    }
}
