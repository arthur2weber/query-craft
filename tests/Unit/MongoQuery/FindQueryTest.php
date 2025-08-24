<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class MongoQueryTopLevelFindQueryTest extends TestCase
{
    public function testBuildFindQueryPagination()
    {
        $q = new MongoQuery();
        $q->take(2)->skip(1);
        $built = $q->build();
        $this->assertArrayHasKey('filter', $built);
        $this->assertArrayHasKey('options', $built);
    }
}
