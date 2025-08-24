<?php

namespace Tests\Unit\ElasticQuery;

use Tests\Unit\ElasticQuery\Utility\TestableElasticQuery;

require_once __DIR__ . '/Utility/TestableElasticQuery.php';

use PHPUnit\Framework\TestCase;

class ClausesConflictTest extends TestCase
{
    public function testAreClausesConflictingTermAndMatch()
    {
        $q = new TestableElasticQuery();

        $c1 = ['term' => ['field' => 'value']];
        $c2 = ['term' => ['field' => 'value']];

        $this->assertTrue($q->callAreClausesConflicting($c1, $c2, 'field', 'value'));

        $c3 = ['match' => ['field' => 'other']];
        $this->assertFalse($q->callAreClausesConflicting($c1, $c3, 'field', 'value'));
    }

    public function testCheckForLogicalConflictsTriggersWarning()
    {
        $q = new TestableElasticQuery();
        // set existing must and must_not
        $q->must(['term' => ['a' => '1']]);
        $q->mustNot(['term' => ['a' => '1']]);

        $warnings = [];
        $prev = set_error_handler(function($errno, $errstr) use (&$warnings) {
            if ($errno === E_USER_WARNING) {
                $warnings[] = $errstr;
                return true;
            }
            return false;
        });

        // calling where should run checkForLogicalConflicts and produce a warning
        $q->where('a', '=', '1');

        restore_error_handler();
        if ($prev !== null) {
            set_error_handler($prev);
        }

        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('Potential logical conflict detected', $warnings[0]);
    }
}
