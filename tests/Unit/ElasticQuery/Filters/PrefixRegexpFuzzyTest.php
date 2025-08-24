<?php

namespace Tests\Unit\ElasticQuery\Filters;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Utility/TestableElasticQuery.php';

class PrefixRegexpFuzzyTest extends TestCase
{
    private function makeTestable()
    {
        return new \Tests\Unit\ElasticQuery\TestableElasticQuery();
    }

    public function testPrefixValidationEmpty()
    {
        $q = $this->makeTestable();
        $this->expectException(\InvalidArgumentException::class);
        $q->prefix('title', '   ');
    }

    public function testRegexpEmptyPatternThrows()
    {
        $q = $this->makeTestable();
        $this->expectException(\InvalidArgumentException::class);
        $q->regexp('f', '   ');
    }

    public function testFuzzyValidation()
    {
        $q = $this->makeTestable();
        $this->expectException(\InvalidArgumentException::class);
        $q->fuzzy('name', '');

        $this->expectException(\InvalidArgumentException::class);
        $q->fuzzy('name', 'bob', new \stdClass());
    }
}
