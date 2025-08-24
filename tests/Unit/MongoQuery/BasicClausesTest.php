<?php

namespace Tests\Unit\MongoQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class BasicClausesTest extends TestCase
{
    public function testTermRangeExistsTermsNotTerms()
    {
        // termClause
        $term = MongoQuery::termClause('a', 1);
        $this->assertSame(['a' => 1], $term);

        // rangeClause
        $range = MongoQuery::rangeClause('b', 'gte', 10);
        $this->assertSame(['b' => ['$gte' => 10]], $range);

        // existsClause
        $exists = MongoQuery::existsClause('c');
        $this->assertSame(['c' => ['$exists' => true]], $exists);

        // termsClause
        $terms = MongoQuery::termsClause('d', [1,2,3]);
        $this->assertSame(['d' => ['$in' => [1,2,3]]], $terms);

        // notTermsClause
        $notTerms = MongoQuery::notTermsClause('e', [4,5]);
        $this->assertSame(['$or' => [['e' => ['$nin' => [4,5]]]]], $notTerms);
    }
}
