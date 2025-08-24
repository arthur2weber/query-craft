<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryFiltersExtraTest extends TestCase
{
    public function testScriptScoreWrapsQuery()
    {
        $q = new ElasticQuery();
        $q->filter('status', 'active');
        $q->scriptScore("return 1;");
        $built = $q->build();
        $this->assertArrayHasKey('script_score', $built['query']);
    }

    public function testMatchBoostAndTermBoost()
    {
        $q = new ElasticQuery();
        $q->matchBoost('title', 'hello', 2.0);
        $q->termBoost('status', 'active', 1.5);
        $this->assertArrayHasKey('query', $q->build());
    }
}
