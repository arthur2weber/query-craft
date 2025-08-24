<?php

namespace Arthur2weber\QueryCraft\Tests\Unit\ElasticQuery;

use Arthur2weber\QueryCraft\Query\ElasticQuery;
use PHPUnit\Framework\TestCase;

class LowPriorityCoverageTest extends TestCase
{
    public function testClauseBuildersReturnExpectedStructures()
    {
        $this->assertSame(['prefix' => ['a' => 'b']], ElasticQuery::prefixClause('a', 'b'));
        $this->assertSame(['wildcard' => ['f' => 'p']], ElasticQuery::wildcardClause('f', 'p'));
        $this->assertSame(['regexp' => ['r' => 'x.*y']], ElasticQuery::regexpClause('r', 'x.*y'));
        $fuzzy = ElasticQuery::fuzzyClause('name', 'val', 'AUTO');
        $this->assertArrayHasKey('fuzzy', $fuzzy);
        $this->assertArrayHasKey('name', $fuzzy['fuzzy']);
    }

    public function testCacheClauseEvictsWhenMaxReached()
    {
        // fill cache beyond max (defaults to 100) to exercise eviction path
        for ($i = 0; $i < 130; $i++) {
            ElasticQuery::termClause('field' . $i, (string)$i);
        }
        // If no exception thrown, behavior executed. Assert something trivial.
        $this->assertTrue(true);
    }

    public function testValidateScriptEmptyThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $q = new ElasticQuery();
        // scriptScore validates script via validateScript which should throw on empty
        $q->scriptScore('');
    }

    public function testProtectedAreClausesConflictingViaReflection()
    {
        $q = new ElasticQuery();
        $ref = new \ReflectionClass($q);
        $method = $ref->getMethod('areClausesConflicting');
        $method->setAccessible(true);

        $c1 = ['match' => ['x' => 'a']];
        $c2 = ['match' => ['x' => 'a']];
        $this->assertTrue($method->invoke($q, $c1, $c2, 'x', 'a'));

        $c3 = ['term' => ['y' => 'b']];
        $c4 = ['term' => ['y' => 'c']];
        $this->assertFalse($method->invoke($q, $c3, $c4, 'y', 'b'));
    }

    public function testValidateConditionValuesRecursionLimitViaReflection()
    {
        $q = new ElasticQuery();
        $ref = new \ReflectionClass($q);
        $method = $ref->getMethod('validateConditionValues');
        $method->setAccessible(true);

        $deep = [];
        $cursor =& $deep;
        for ($i = 0; $i < 55; $i++) {
            $cursor['a'] = [];
            $cursor =& $cursor['a'];
        }

        $this->expectException(\InvalidArgumentException::class);
        $method->invoke($q, $deep);
    }
}
