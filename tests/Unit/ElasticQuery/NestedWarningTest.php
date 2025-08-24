<?php

namespace Tests\Unit\ElasticQuery;

require_once __DIR__ . '/TestableElasticQuery.php';

use PHPUnit\Framework\TestCase;

class NestedWarningTest extends TestCase
{
    public function testNestedEmptyCallbackEmitsWarning()
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

        $q->nested('path', function($n) {
            // do nothing leaving nested query empty
        });

        restore_error_handler();
        if ($prev !== null) {
            set_error_handler($prev);
        }

        $this->assertNotEmpty($warnings, 'Expected a user warning from nested empty callback');
        $this->assertStringContainsString('Nested query callback produced an empty query', $warnings[0]);
    }
}
