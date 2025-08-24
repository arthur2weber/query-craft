<?php

namespace Arthur2weber\QueryCraft\Tests\Unit\ElasticQuery;

use Arthur2weber\QueryCraft\Query\ElasticQuery;
use PHPUnit\Framework\TestCase;

class ValidateAggregationAndWarningsTest extends TestCase
{
    private function captureWarning(callable $fn): array
    {
        $warnings = [];
        $h = function ($errno, $errstr) use (&$warnings) {
            $warnings[] = $errstr;
            return true;
        };
        set_error_handler($h, E_USER_WARNING);
        try { $fn(); } finally { restore_error_handler(); }
        return $warnings;
    }

    public function testAggregationEmptyNameOrConfigThrows()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->aggregation('', []);
    }

    public function testUnknownAggregationTypeTriggersWarning()
    {
        $q = new ElasticQuery();
        $warnings = $this->captureWarning(function () use ($q) {
            $q->aggregation('x', ['unknown_agg' => ['field' => 'a']]);
        });
        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('Unknown aggregation type', $warnings[0]);
    }

    public function testVerbosePerformanceWarningsAreCapturedInGetWarnings()
    {
        $q = new ElasticQuery();
        $q->verbose(true);
        // use a very large size to trigger the performance warning path
        $q->size(20000);
        $this->assertNotEmpty($q->getPerformanceWarnings());
        $all = $q->getWarnings();
        $this->assertNotEmpty($all);
    }
}
