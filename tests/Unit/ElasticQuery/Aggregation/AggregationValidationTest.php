<?php

namespace Tests\Unit\ElasticQuery\Aggregation;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Utility/TestableElasticQuery.php';

class AggregationValidationTest extends TestCase
{
    private function makeTestable()
    {
        return new \Tests\Unit\ElasticQuery\TestableElasticQuery();
    }

    public function testValidateAggregationThrowsOnEmptyNameOrConfig()
    {
        $q = $this->makeTestable();

        $this->expectException(\InvalidArgumentException::class);
        $q->callValidateAggregation('', ['terms' => []]);

        $this->expectException(\InvalidArgumentException::class);
        $q->callValidateAggregation('name', []);
    }

    public function testUnknownAggregationTypeIssuesWarning()
    {
        $q = $this->makeTestable();
        $warnings = [];
        $handler = function($errno, $errstr) use (&$warnings) {
            if ($errno === E_USER_WARNING) {
                $warnings[] = $errstr;
                return true;
            }
            return false;
        };
        set_error_handler($handler, E_USER_WARNING);
        try {
            $q->aggregation('foo', ['unknown_type' => []]);
        } finally {
            restore_error_handler();
        }
        $this->assertNotEmpty($warnings, 'Expected a user warning for unknown aggregation type');
    }
}
