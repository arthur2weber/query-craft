<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryToQueryTest extends TestCase
{
    public function testToQueryReturnsBuiltArray()
    {
        $q = new ElasticQuery();
        $q->filter('status', 'active')->size(5);

        $built = $q->build();
        $query = $q->toQuery();

        $this->assertIsArray($query);
        $this->assertEquals($built, $query);
    }
}
