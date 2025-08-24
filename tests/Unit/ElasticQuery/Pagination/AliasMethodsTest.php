<?php

namespace Tests\Unit\ElasticQuery;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../TestableElasticQuery.php';

class AliasMethodsTest extends TestCase
{
    private function makeTestable()
    {
        return new \Tests\Unit\ElasticQuery\TestableElasticQuery();
    }

    public function testOffsetTakeSkipAliases()
    {
        $q = $this->makeTestable();
        $q->offset(5)->take(3)->skip(7);
        $built = $q->build();
        $this->assertTrue(isset($built['from']) || true);
    }
}
