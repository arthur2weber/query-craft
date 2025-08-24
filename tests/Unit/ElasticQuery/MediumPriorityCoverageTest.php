<?php

namespace Arthur2weber\QueryCraft\Tests\Unit\ElasticQuery;

use Arthur2weber\QueryCraft\Query\ElasticQuery;
use PHPUnit\Framework\TestCase;

class MediumPriorityCoverageTest extends TestCase
{
    public function testScriptScoreWrapsQuery()
    {
        $q = new ElasticQuery();
        $q->match('title', 'hello');
        $q->scriptScore("doc['views'].value * 1");
        $built = $q->build();
        $this->assertArrayHasKey('script_score', $built['query']);
        $this->assertArrayHasKey('script', $built['query']['script_score']);
    }

    public function testExistsAddsMustClause()
    {
        $q = new ElasticQuery();
        $q->exists('published_at');
        $built = $q->build();
        $this->assertArrayHasKey('must', $built['query']['bool']);
        $this->assertArrayHasKey('exists', $built['query']['bool']['must'][0]);
        $this->assertSame('published_at', $built['query']['bool']['must'][0]['exists']['field']);
    }

    public function testRangeInvalidOperatorThrows()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->range('age', 'invalid_op', 21);
    }

    public function testRangeBoostCreatesClause()
    {
        $q = new ElasticQuery();
        $q->rangeBoost('age', 'gte', 18, 1.5);
        $built = $q->build();
        $this->assertArrayHasKey('must', $built['query']['bool']);
        $range = $built['query']['bool']['must'][0]['range'];
        $this->assertArrayHasKey('age', $range);
        $this->assertArrayHasKey('boost', $range['age']);
        $this->assertSame(1.5, $range['age']['boost']);
    }

    public function testNestedBuildsNestedClause()
    {
        $q = new ElasticQuery();
        $q->nested('comments', function($sub) {
            $sub->match('comments.text', 'great');
        });
        $built = $q->build();
        $this->assertArrayHasKey('must', $built['query']['bool']);
        $nested = $built['query']['bool']['must'][0]['nested'];
        $this->assertSame('comments', $nested['path']);
        $this->assertArrayHasKey('query', $nested);
    }

    public function testToDslProducesJson()
    {
        $q = new ElasticQuery();
        $q->match('body', 'lorem');
        $dsl = $q->toDSL();
        $this->assertIsString($dsl);
        $this->assertStringContainsString('"query"', $dsl);
        $this->assertNotFalse(json_decode($dsl));
    }

    public function testVerboseModeLogsAndGetWarnings()
    {
        $q = new ElasticQuery();
        $q->verbose(true);
        $q->match('title', 'x');
        $logs = $q->getVerboseLog();
        $this->assertNotEmpty($logs);

        // performance warnings via large size
        $q2 = new ElasticQuery();
        $q2->size(20001);
        $this->assertNotEmpty($q2->getPerformanceWarnings());
        $this->assertNotEmpty($q2->getWarnings());
    }
}
