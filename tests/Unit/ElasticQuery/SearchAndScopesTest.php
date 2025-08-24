<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQuerySearchAndScopesTest extends TestCase
{
    public function testSearchInAndSearchPhrase()
    {
        $q = new ElasticQuery();
        $q->searchIn('title', 'machine learning');
        $q->searchPhrase('content', 'step by step tutorial');
        $built = $q->build();

        $this->assertArrayHasKey('query', $built);
        $this->assertTrue(isset($built['query']['bool']['must'][0]['match']['title']));
        $this->assertTrue(isset($built['query']['bool']['must'][1]['match_phrase']['content']));
    }

    public function testPredefinedScopesActivePublishedRecent()
    {
        $q = new ElasticQuery();
        $q->active();
        $built = $q->build();
        $this->assertEquals('active', $built['query']['bool']['filter'][0]['term']['status']);

        $q2 = new ElasticQuery();
        $q2->published();
        $built2 = $q2->build();
        $this->assertEquals('published', $built2['query']['bool']['filter'][0]['term']['status']);
        $this->assertArrayHasKey('range', $built2['query']['bool']['filter'][1]);

        $q3 = new ElasticQuery();
        $q3->recent(7);
        $built3 = $q3->build();
        $this->assertArrayHasKey('range', $built3['query']['bool']['filter'][0]);
    }
}
