<?php

namespace Arthur2weber\QueryCraft\Tests\Unit\ElasticQuery;

use Arthur2weber\QueryCraft\Query\ElasticQuery;
use PHPUnit\Framework\TestCase;

class WarningsTest extends TestCase
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
        $q->must(['term' => ['status' => 'active']]);
        $q->mustNot(['term' => ['status' => 'active']]);

        $warnings = $this->captureWarning(function () use ($q) {
            $q->where('status', '=', 'active');
        });

        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('Potential logical conflict detected', $warnings[0]);
    }
}
