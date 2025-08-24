<?php

namespace Tests\Unit\ElasticQuery\Filters;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

require_once __DIR__ . '/../Utility/TestableElasticQuery.php';

class ElasticQueryFiltersBasicQueryTest extends TestCase
{
    public function testBasicWhereAndBuild()
    {
        $q = new ElasticQuery();
        $q->filter('status', 'active');
        $built = $q->build();
        $this->assertArrayHasKey('query', $built);
    }

    public function testMatchAndSearch()
    {
        $q = new ElasticQuery();
        $q->match('title', 'hello');
        $analysis = $q->analyze();
        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('has_search', $analysis);
    }
}
