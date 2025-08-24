<?php

namespace Tests\Unit\ElasticQuery;

use PHPUnit\Framework\TestCase;

class PerformanceWarningsLeadingWildcardTest extends TestCase
{
    public function testLeadingWildcardAddsPerformanceWarning()
    {
        $q = new TestableElasticQuery();
        $q->wildcard('name', '*smith');
        $warnings = $q->getPerformanceWarnings();
        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('Leading wildcard query', $warnings[0]);
    }

    public function testRegexAddsPerformanceWarning()
    {
        $q = new TestableElasticQuery();
        $q->regexp('name', 'foo.*');
        $warnings = $q->getPerformanceWarnings();
        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('Regex queries can be slow', $warnings[0]);
    }
}
