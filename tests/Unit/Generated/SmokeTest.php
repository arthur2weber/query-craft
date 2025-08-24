<?php

namespace Tests\Unit\Generated;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;
use Arthur2weber\QueryCraft\Query\MongoQuery;
use Arthur2weber\QueryCraft\Query\GraphQuery;
use Arthur2weber\QueryCraft\QueryCraft;

class SmokeTest extends TestCase
{
    public function testQueryCraftFactoryAndHelpers()
    {
        $this->assertInstanceOf(ElasticQuery::class, QueryCraft::elastic());
        $this->assertInstanceOf(MongoQuery::class, QueryCraft::mongo());
        $this->assertInstanceOf(GraphQuery::class, QueryCraft::graph());

        $this->assertTrue(QueryCraft::supports('elasticsearch'));
        $this->assertTrue(QueryCraft::supports('mongodb'));
        $this->assertTrue(QueryCraft::supports('graphql'));

        $available = QueryCraft::available();
        $this->assertIsArray($available);
        $this->assertArrayHasKey('elasticsearch', $available);
        $this->assertIsString(QueryCraft::version());

        // test for() accepts alias
        $this->assertInstanceOf(ElasticQuery::class, QueryCraft::for('elastic'));
        $this->assertInstanceOf(MongoQuery::class, QueryCraft::for('mongo'));
        $this->assertInstanceOf(GraphQuery::class, QueryCraft::for('graph'));
    }
}
