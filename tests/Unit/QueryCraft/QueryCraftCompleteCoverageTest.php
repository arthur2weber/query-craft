<?php

namespace Tests\Unit\QueryCraft;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;
use Arthur2weber\QueryCraft\Query\ElasticQuery;
use Arthur2weber\QueryCraft\Query\MongoQuery;
use Arthur2weber\QueryCraft\Query\GraphQuery;

class QueryCraftCompleteCoverageTest extends TestCase
{
    public function testAllFactoryAliasesAndHelpers()
    {
        // factories
        $this->assertInstanceOf(ElasticQuery::class, QueryCraft::elastic());
        $this->assertInstanceOf(MongoQuery::class, QueryCraft::mongo());
        $this->assertInstanceOf(GraphQuery::class, QueryCraft::graph());

        // for() with full names
        $this->assertInstanceOf(ElasticQuery::class, QueryCraft::for('elasticsearch'));
        $this->assertInstanceOf(MongoQuery::class, QueryCraft::for('mongodb'));
        $this->assertInstanceOf(GraphQuery::class, QueryCraft::for('graphql'));

        // for() with short aliases
        $this->assertInstanceOf(ElasticQuery::class, QueryCraft::for('elastic'));
        $this->assertInstanceOf(MongoQuery::class, QueryCraft::for('mongo'));
        $this->assertInstanceOf(GraphQuery::class, QueryCraft::for('graph'));

        // available() returns mapping
        $available = QueryCraft::available();
        $this->assertIsArray($available);
        $this->assertArrayHasKey('elasticsearch', $available);
        $this->assertArrayHasKey('mongodb', $available);
        $this->assertArrayHasKey('graphql', $available);

        // supports() true for known entries, false for unknown
        $this->assertTrue(QueryCraft::supports('elasticsearch'));
        $this->assertTrue(QueryCraft::supports('mongodb'));
        $this->assertTrue(QueryCraft::supports('graphql'));
        $this->assertFalse(QueryCraft::supports('nonexistent'));

        // version() returns expected semantic string
        $version = QueryCraft::version();
        $this->assertIsString($version);
        $this->assertStringContainsString('2.0', $version);
    }
}
