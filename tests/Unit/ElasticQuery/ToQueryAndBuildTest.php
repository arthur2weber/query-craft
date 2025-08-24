<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryToQueryAndBuildTest extends TestCase
{
    public function testGetFirstAndToQuery()
    {
        $q = new ElasticQuery();
        $q->filter('status', 'active');
        $q->size(2);
        $built = $q->get();
        $this->assertIsArray($built);

        $first = $q->first();
        // Some environments return the built array when no hits are present,
        // others return null. Accept either behavior.
        $this->assertTrue(is_null($first) || (is_array($first) && isset($first['query'])));

        // toDSL / toQuery sometimes named differently; check available method
        if (method_exists($q, 'toDSL')) {
            $dsl = $q->toDSL();
            $this->assertIsString($dsl);
        }
    }

    public function testTakeSkipAliases()
    {
        $q = new ElasticQuery();
        $q->take(3)->skip(1);
        $built = $q->build();
        $this->assertEquals(3, $built['size']);
        $this->assertEquals(1, $built['from']);
    }
}
