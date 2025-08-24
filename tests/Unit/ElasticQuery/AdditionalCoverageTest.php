<?php

namespace Arthur2weber\QueryCraft\Tests\Unit\ElasticQuery;

use Arthur2weber\QueryCraft\Query\ElasticQuery;
use PHPUnit\Framework\TestCase;

class AdditionalCoverageTest extends TestCase
{
    private function captureWarning(callable $fn): array
    {
        $warnings = [];
        $handler = function ($errno, $errstr) use (&$warnings) {
            $warnings[] = $errstr;
            return true; // prevent PHPUnit from converting to error
        };
        set_error_handler($handler, E_USER_WARNING);
        try {
            $fn();
        } finally {
            restore_error_handler();
        }
        return $warnings;
    }

    public function testNestedEmptyCallbackProducesWarning()
    {
        $q = new ElasticQuery();
        $warnings = $this->captureWarning(function () use ($q) {
            // nested with empty callback should trigger a user warning
            $q->nested('parent', function ($nested) {
                // do nothing - produce empty nested query
            });
        });

        $this->assertNotEmpty($warnings, 'Expected at least one warning from nested empty callback');
        $this->assertStringContainsString('Nested query callback produced an empty query', $warnings[0]);
    }

    // Note: passing a resource to filter() will hit PHP's type checks and raise TypeError
    public function testValidateValueRejectsResource()
    {
        $q = new ElasticQuery();

        // resource should throw TypeError due to method signature
        $this->expectException(\TypeError::class);
        $res = fopen('php://memory', 'r');
        try {
            $q->filter('f', $res);
        } finally {
            if (is_resource($res)) {
                fclose($res);
            }
        }
    }

    public function testValidateValueRejectsNaNAndInfinity()
    {
        $q = new ElasticQuery();

        $this->expectException(\InvalidArgumentException::class);
        $q->where('n', '>', NAN);
    }

    public function testValidateValueRejectsInfinity()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->where('n', '>', INF);
    }

    public function testValidateValueRejectsNonUtf8AndControlChars()
    {
        $q = new ElasticQuery();

        // non-utf8
        $this->expectException(\InvalidArgumentException::class);
        $invalid = pack('C*', 0x80);
        $q->match('title', $invalid);
    }

    public function testValidateValueRejectsControlChars()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->match('title', "hello\x01world");
    }

    public function testVeryLongStringTriggersWarning()
    {
        $q = new ElasticQuery();
        $long = str_repeat('a', 40000);
        $warnings = $this->captureWarning(function () use ($q, $long) {
            // filter triggers validateValue which will emit a user warning for long strings
            $q->filter('long_field', $long);
        });

        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('String value is very long', $warnings[0]);
    }

    public function testValidateAggregationUnknownTypeTriggersWarning()
    {
        $q = new ElasticQuery();
        $warnings = $this->captureWarning(function () use ($q) {
            $q->aggregation('a1', ['mystery_agg' => ['field' => 'x']]);
        });

        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString("Unknown aggregation type", $warnings[0]);
    }

    public function testGeoBoundingBoxValidationAndBadCoordinates()
    {
        $q = new ElasticQuery();
        // valid bbox should not throw
        $q->must(['geo_bounding_box' => [
            'loc' => [
                'top_left' => ['lat' => 10, 'lon' => 20],
                'bottom_right' => ['lat' => -10, 'lon' => -20]
            ]
        ]]);

        // invalid coordinates should throw
        $this->expectException(\InvalidArgumentException::class);
        $q->must(['geo_bounding_box' => [
            'loc' => [
                'top_left' => ['lat' => 1000, 'lon' => 20], // invalid latitude
                'bottom_right' => ['lat' => -10, 'lon' => -20]
            ]
        ]]);
    }

    public function testVerboseModeAddsVerboseLogAndWarningsAppearInGetWarnings()
    {
        $q = new ElasticQuery();
        $q->verbose(true);
        $q->search('', ['title']); // empty search adds a warning

        $warnings = $q->getWarnings();
        $this->assertNotEmpty($warnings);

        $logs = $q->getVerboseLog();
        $this->assertNotEmpty($logs);
        $found = false;
        foreach ($logs as $entry) {
            if (strpos($entry, 'WARNING') !== false || strpos($entry, '⚠️') !== false) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected verbose log to contain a warning entry');
    }
}
