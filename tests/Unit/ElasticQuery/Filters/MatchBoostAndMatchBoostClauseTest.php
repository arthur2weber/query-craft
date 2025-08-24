<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class MatchBoostAndMatchBoostClauseTest extends TestCase
{
    public function testMatchBoostCreatesBoostedMatch()
    {
        $q = new ElasticQuery();
        $q->matchBoost('title', 'phpunit', 2.5);

        $built = $q->toQuery();

        $this->assertIsArray($built);
        $this->assertStringContainsString('"match"', json_encode($built));
        $this->assertStringContainsString('2.5', json_encode($built));
    }

    public function testMatchBoostClauseProducesMatchWithBoost()
    {
        $q = new ElasticQuery();
        // use static clause builder and add via must() so it becomes part of the query
        $q->must(ElasticQuery::matchBoostClause('description', 'testing', 1.2));

        $built = $q->toQuery();

        $this->assertIsArray($built);
        $this->assertStringContainsString('"match"', json_encode($built));
        $this->assertStringContainsString('1.2', json_encode($built));
    }
}
