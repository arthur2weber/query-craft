<?php

namespace Tests\Unit\ElasticQuery;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../TestableElasticQuery.php';

class WhenUnlessTest extends TestCase
{
    private function makeTestable()
    {
        return new \Tests\Unit\ElasticQuery\TestableElasticQuery();
    }

    public function testWhenUnlessBehavior()
    {
        $q = $this->makeTestable();
        $q->when(true, function($qb) {
            return $qb->where('status', 'active');
        });
        $this->assertArrayHasKey('query', $q->build());

        $q2 = $this->makeTestable();
        $q2->unless(true, function($qb) {
            return $qb->where('status', 'inactive');
        });
        $built = $q2->build();
        $this->assertArrayHasKey('query', $built);
    }
}
