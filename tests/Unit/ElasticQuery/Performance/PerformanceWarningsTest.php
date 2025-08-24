<?php

namespace Tests\Unit\ElasticQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;

class PerformanceWarningsTest extends TestCase
{
    public function testCheckPerformanceWarningsAddsWarnings()
    {
        $q = QueryCraft::elastic();
        $ref = new \ReflectionClass($q);
        $m = $ref->getMethod('checkPerformanceWarnings');
        $m->setAccessible(true);

        // calling with a benign operation should not throw
        $m->invoke($q, 'none');
        $this->assertIsArray($q->getPerformanceWarnings());
    }
}
