<?php

namespace Tests\Unit\MongoQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class HelpersTest extends TestCase
{
    public function testTermRangeExistsTermsNotTermsRegexPrefixWildcardAndBoostWrappers()
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

        // regexClause with options
        $regex = MongoQuery::regexClause('f', '^abc', 'i');
        $this->assertSame(['f' => ['$regex' => '^abc', '$options' => 'i']], $regex);

        // matchStage / matchClause
        $matchStage = MongoQuery::matchStage(['x' => 1]);
        $this->assertSame(['$match' => ['x' => 1]], $matchStage);
        $matchClause = MongoQuery::matchClause('x', 1);
        $this->assertSame(['x' => 1], $matchClause);

        // textClause
        $text = MongoQuery::textClause('hello', ['language' => 'pt']);
        $this->assertSame(['$text' => ['$search' => 'hello', 'language' => 'pt']], $text);

        // prefixClause
        $prefix = MongoQuery::prefixClause('g', 'pre');
        $this->assertArrayHasKey('g', $prefix);
        $this->assertArrayHasKey('$regex', $prefix['g']);

        // wildcardClause
        $wc = MongoQuery::wildcardClause('h', 'a*b?c');
        $this->assertArrayHasKey('h', $wc);
        $this->assertArrayHasKey('$regex', $wc['h']);

        // termBoost / matchBoost wrappers
        $tb = MongoQuery::termBoost('i', 'v', 2.5);
        $this->assertSame(['i' => 'v'], $tb);
        $mb = MongoQuery::matchBoost(['j' => 1], 3.0);
        $this->assertSame(['$match' => ['j' => 1]], $mb);
    }
}
