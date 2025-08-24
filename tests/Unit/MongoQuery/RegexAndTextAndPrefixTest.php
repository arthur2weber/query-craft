<?php

namespace Tests\Unit\MongoQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class RegexAndTextAndPrefixTest extends TestCase
{
    public function testRegexTextPrefixWildcard()
    {
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
    }
}
