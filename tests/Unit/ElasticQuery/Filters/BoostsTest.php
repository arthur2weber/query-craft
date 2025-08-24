<?php

namespace Tests\Unit\ElasticQuery\Filters;

require_once __DIR__ . '/../Utility/TestableElasticQuery.php';

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class BoostsTest extends TestCase
{
    public function testMatchBoostAndTermBoost()
    {
        $q = new ElasticQuery();
        $q->matchBoost('title', 'hello', 2.0);
        $q->termBoost('status', 'active', 1.5);
        $this->assertArrayHasKey('query', $q->build());
    }
}
