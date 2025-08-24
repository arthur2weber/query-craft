<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryWhereNotInAndTermsTest extends TestCase
{
    public function testTermsClauseCachingAndStructure()
    {
        // termsClause static builder
        $terms = ElasticQuery::termsClause('tags', ['php', 'unit']);
        $this->assertArrayHasKey('terms', $terms);
        $this->assertIsArray($terms['terms']['tags']);
        $this->assertCount(2, $terms['terms']['tags']);

        // ensure termClause caching returns same structure and caches
        $t1 = ElasticQuery::termClause('status', 'published');
        $t2 = ElasticQuery::termClause('status', 'published');
        $this->assertSame($t1, $t2);
    }

    public function testWhereNotInCreatesMustNotTerms()
    {
        $q = new ElasticQuery();
        $q->whereNotIn('status', ['deleted', 'spam']);
        $built = $q->build();
        $this->assertArrayHasKey('query', $built);
        $this->assertArrayHasKey('bool', $built['query']);
        $this->assertArrayHasKey('must_not', $built['query']['bool']);
        $this->assertEquals(['deleted', 'spam'], $built['query']['bool']['must_not'][0]['terms']['status']);
    }

    public function testWhereNotInValidations()
    {
        $this->expectException(InvalidArgumentException::class);
        (new ElasticQuery())->whereNotIn('field', []);
    }
}
