<?php

namespace Tests\Unit\ElasticQuery\Filters;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Utility/TestableElasticQuery.php';

class TermsSourceTest extends TestCase
{
    private function makeTestable()
    {
        return new \Tests\Unit\ElasticQuery\TestableElasticQuery();
    }

    public function testTermsAndSourceValidation()
    {
        $q = $this->makeTestable();

        $this->expectException(\InvalidArgumentException::class);
        $q->terms('tags', []);

        $this->expectException(\InvalidArgumentException::class);
        $q->source([]);

        // valid terms
        $q->terms('tags', ['a','b']);
        $this->assertArrayHasKey('query', $q->build());
    }
}
