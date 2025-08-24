<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ExistsAndFuzzyClauseTest extends TestCase
{
    public function testExistsAddsExistsClauseInBuiltQuery()
    {
        $q = new ElasticQuery();
        $q->exists('published_at');

        $query = $q->toQuery();

        $this->assertIsArray($query);
        $this->assertStringContainsString('"exists"', json_encode($query));
    }

    public function testFuzzyClauseProducesFuzzyInQuery()
    {
        $q = new ElasticQuery();
        // using the instance fuzzy method which appends the clause
        $q->fuzzy('name', 'jon', 2);

        $query = $q->toQuery();

        $this->assertIsArray($query);
        $this->assertStringContainsString('"fuzzy"', json_encode($query));
        $this->assertStringContainsString('jon', json_encode($query));
    }
}
