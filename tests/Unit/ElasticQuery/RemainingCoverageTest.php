<?php

namespace Arthur2weber\QueryCraft\Tests\Unit\ElasticQuery;

use Arthur2weber\QueryCraft\Query\ElasticQuery;
use PHPUnit\Framework\TestCase;

class RemainingCoverageTest extends TestCase
{
    public function testAddWarningAndPerformanceWarningViaReflection()
    {
        $q = new ElasticQuery();
        $ref = new \ReflectionClass($q);

        $addWarning = $ref->getMethod('addWarning');
        $addWarning->setAccessible(true);
        $addWarning->invoke($q, 'test warning message');

        $addPerf = $ref->getMethod('addPerformanceWarning');
        $addPerf->setAccessible(true);
        $addPerf->invoke($q, 'test perf message');

        $this->assertNotEmpty($q->getWarnings());
        $this->assertNotEmpty($q->getPerformanceWarnings());

        $this->assertStringContainsString('test warning message', implode(' ', $q->getWarnings()));
        $this->assertStringContainsString('test perf message', implode(' ', $q->getPerformanceWarnings()));
    }

    public function testCheckPerformanceWarningsBranchesViaReflection()
    {
        $q = new ElasticQuery();
        $ref = new \ReflectionClass($q);
        $method = $ref->getMethod('checkPerformanceWarnings');
        $method->setAccessible(true);

        // wildcard leading
        $method->invoke($q, 'wildcard', ['value' => '*abc']);
        $this->assertNotEmpty($q->getPerformanceWarnings());
        $this->assertStringContainsString('Leading wildcard', implode(' ', $q->getPerformanceWarnings()));

        // regex
        $q2 = new ElasticQuery();
        $method->invoke($q2, 'regex', []);
        $this->assertNotEmpty($q2->getPerformanceWarnings());
        $this->assertStringContainsString('Regex queries can be slow', implode(' ', $q2->getPerformanceWarnings()));

        // pagination deep and large size
        $q3 = new ElasticQuery();
        $method->invoke($q3, 'pagination', ['from' => 20000, 'size' => 500]);
        $this->assertNotEmpty($q3->getPerformanceWarnings());
        $joined = implode(' ', $q3->getPerformanceWarnings());
        $this->assertStringContainsString('Deep pagination', $joined);
        $this->assertStringContainsString('Large page size', $joined);

        // sort_script
        $q4 = new ElasticQuery();
        $method->invoke($q4, 'sort_script', []);
        $this->assertNotEmpty($q4->getPerformanceWarnings());
        $this->assertStringContainsString('Script-based sorting', implode(' ', $q4->getPerformanceWarnings()));

        // nested_aggregation depth
        $q5 = new ElasticQuery();
        $method->invoke($q5, 'nested_aggregation', ['depth' => 5]);
        $this->assertNotEmpty($q5->getPerformanceWarnings());
        $this->assertStringContainsString('Deep nested aggregations', implode(' ', $q5->getPerformanceWarnings()));
    }
}
