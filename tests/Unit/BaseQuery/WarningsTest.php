<?php

namespace Tests\Unit\BaseQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;

class WarningsTest extends TestCase
{
    public function testAddWarningAndPerformanceWarningViaReflection()
    {
        // Use ElasticQuery instance (inherits BaseQuery)
        $instance = QueryCraft::elastic();

        $ref = new \ReflectionClass($instance);

        // Test addWarning
        $addWarning = $ref->getMethod('addWarning');
        $addWarning->setAccessible(true);
        $addWarning->invoke($instance, 'This is a test warning');

        $warnings = $instance->getWarnings();
        $this->assertIsArray($warnings);
        $this->assertContains('This is a test warning', $warnings);

        // Test addPerformanceWarning
        $addPerf = $ref->getMethod('addPerformanceWarning');
        $addPerf->setAccessible(true);
        $addPerf->invoke($instance, 'Perf warning test');

        $perfWarnings = $instance->getPerformanceWarnings();
        $this->assertIsArray($perfWarnings);
        $this->assertContains('Perf warning test', $perfWarnings);
    }
}
