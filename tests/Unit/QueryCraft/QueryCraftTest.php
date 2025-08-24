<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;
use Arthur2weber\QueryCraft\Query\ElasticQuery;
use Arthur2weber\QueryCraft\Query\MongoQuery;
use Arthur2weber\QueryCraft\Query\GraphQuery;

class QueryCraftTest extends TestCase
{
    public function testFactoriesReturnCorrectInstances()
    {
        $this->assertInstanceOf(ElasticQuery::class, QueryCraft::elastic());
        $this->assertInstanceOf(MongoQuery::class, QueryCraft::mongo());
        $this->assertInstanceOf(GraphQuery::class, QueryCraft::graph());
    }

    public function testForAndSupportsAndAvailable()
    {
        $this->assertInstanceOf(ElasticQuery::class, QueryCraft::for('elastic'));
        $this->assertTrue(QueryCraft::supports('elasticsearch'));
        $this->assertFalse(QueryCraft::supports('does-not-exist'));

        $available = QueryCraft::available();
        $this->assertArrayHasKey('elasticsearch', $available);
        $this->assertArrayHasKey('mongodb', $available);
        $this->assertArrayHasKey('graphql', $available);
    }

    public function testForThrowsOnUnsupported()
    {
        $this->expectException(InvalidArgumentException::class);
        QueryCraft::for('unknown-type');
    }

    public function testVersion()
    {
        $this->assertIsString(QueryCraft::version());
        $this->assertStringContainsString('2.0', QueryCraft::version());
    }
}
