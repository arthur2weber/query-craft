<?php

namespace Arthur2weber\QueryCraft\Tests\Unit\ElasticQuery;

use Arthur2weber\QueryCraft\Query\ElasticQuery;
use PHPUnit\Framework\TestCase;

class MoreCoverageTest extends TestCase
{
    private function captureWarning(callable $fn): array
    {
        $warnings = [];
        $handler = function ($errno, $errstr) use (&$warnings) {
            $warnings[] = $errstr;
            return true;
        };
        set_error_handler($handler, E_USER_WARNING);
        try {
            $fn();
        } finally {
            restore_error_handler();
        }
        return $warnings;
    }

    public function testFieldNameWithSpacesEmitsWarning()
    {
        $q = new ElasticQuery();
        $warnings = $this->captureWarning(function () use ($q) {
            $q->filter('bad field', 'value');
        });

        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('contains spaces', $warnings[0]);
    }

    public function testCheckForLogicalConflictsTriggersWarning()
    {
        $q = new ElasticQuery();
        // add a must clause directly
        $q->must(['term' => ['status' => 'active']]);
        // also add a must_not clause so both sides exist before calling where()
        $q->mustNot(['term' => ['status' => 'active']]);

        $warnings = $this->captureWarning(function () use ($q) {
            // calling where will run checkForLogicalConflicts and should detect the conflict
            $q->where('status', '=', 'active');
        });

        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('Potential logical conflict detected', $warnings[0]);
    }

    public function testValidateConditionValuesRecursionLimitThrows()
    {
        $q = new ElasticQuery();
        // build a very deep nested array (>50)
        $deep = [];
        $ref =& $deep;
        for ($i = 0; $i < 60; $i++) {
            $ref['level'] = [];
            $ref =& $ref['level'];
        }

        $m = new \ReflectionMethod(ElasticQuery::class, 'validateConditionValues');
        $m->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $m->invokeArgs($q, [$deep, 0, 50]);
    }

    public function testTimeoutInvalidFormatThrows()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->timeout('30seconds');
    }

    public function testValidateScriptEmptyThrows()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->scriptScore('   ');
    }
}
