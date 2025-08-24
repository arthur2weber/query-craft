<?php

namespace Tests\Unit\ElasticQuery;

require_once __DIR__ . '/TestableElasticQuery.php';

use PHPUnit\Framework\TestCase;

class AnalyzeAndFirstTest extends TestCase
{
    public function testAnalyzeComplexityLevels()
    {
        $q = new TestableElasticQuery();
        // no clauses
        $a = $q->analyze();
        $this->assertEquals('low', $a['estimated_complexity']);

        // add many clauses to increase complexity
        for ($i = 0; $i < 11; $i++) {
            $q->must(['term' => ['f' . $i => 'v']]);
        }
        $a2 = $q->analyze();
        $this->assertEquals('high', $a2['estimated_complexity']);
    }

    public function testFirstReturnsBuiltOrNull()
    {
        $q = new TestableElasticQuery();
        $res = $q->first();
        // with empty build() it should return null or built array; ensure no exception
        $this->assertTrue($res === null || is_array($res));
    }
}
