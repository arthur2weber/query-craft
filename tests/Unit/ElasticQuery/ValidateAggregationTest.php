<?php

namespace Tests\Unit\ElasticQuery;

use PHPUnit\Framework\TestCase;

class ValidateAggregationTest extends TestCase
{
    public function testValidateAggregationThrowsOnEmptyName()
    {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->callValidateAggregation('', ['terms' => ['field' => 'a']]);
    }

    public function testValidateAggregationWarnsOnUnknownType()
    {
        $q = new TestableElasticQuery();

        $warnings = [];
        $prev = set_error_handler(function($errno, $errstr) use (&$warnings) {
            if ($errno === E_USER_WARNING) {
                $warnings[] = $errstr;
                return true;
            }
            return false;
        });

        $q->callValidateAggregation('x', ['unknown_type' => ['field' => 'a']]);

        restore_error_handler();
        if ($prev !== null) {
            set_error_handler($prev);
        }

        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('Unknown aggregation type', $warnings[0]);
    }
}
