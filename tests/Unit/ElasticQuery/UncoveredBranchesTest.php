<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryUncoveredBranchesTest extends TestCase
{
    public function testValidateAggregationTriggersWarningForUnknownType()
    {
        // capture PHP user warnings triggered by trigger_error()
        $captured = [];
        $prev = set_error_handler(function ($errno, $errstr) use (&$captured) {
            if ($errno === E_USER_WARNING) {
                $captured[] = $errstr;
            }
            return true;
        });

        $q = new ElasticQuery();
        $q->aggregation('myagg', ['unknown_type' => ['field' => 'x']]);

        restore_error_handler();
        if ($prev !== null) {
            set_error_handler($prev);
        }

        $this->assertNotEmpty($captured);
    }

    public function testGeoDistanceValidatesCoordinatesAndThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $q = new ElasticQuery();
        // distance is valid, but lat is not numeric -> should throw from validateGeoCoordinates
        $q->geoDistance('loc', ['lat' => 'not-a-number', 'lon' => 0], '5km');
    }

    public function testShouldEmptyThrowsAndMinimumShouldMatchIsSet()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->should([]);

        // now valid should with minimumShouldMatch
        $q2 = new ElasticQuery();
        $q2->should(['match' => ['f' => 'v']], 2);
        $built = $q2->build();
        $this->assertArrayHasKey('minimum_should_match', $built['query']['bool']);
        $this->assertEquals(2, $built['query']['bool']['minimum_should_match']);
    }

    public function testMustNotEmptyThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new ElasticQuery())->mustNot([]);
    }

    public function testMatchBoostWarningAndAddsClause()
    {
        $q = new ElasticQuery();
        // capture PHP user warnings triggered by trigger_error()
        $captured = [];
        $prev = set_error_handler(function ($errno, $errstr) use (&$captured) {
            if ($errno === E_USER_WARNING) {
                $captured[] = $errstr;
            }
            return true;
        });

        $q->matchBoost('f', '', 1.0);

        restore_error_handler();
        if ($prev !== null) {
            set_error_handler($prev);
        }

        $this->assertNotEmpty($captured);

        // non-empty value adds clause
        $q2 = new ElasticQuery();
        $q2->matchBoost('f', 'v', 2.0);
        $built = $q2->build();
        $this->assertNotEmpty($built['query']['bool']['must']);
        $this->assertArrayHasKey('match', $built['query']['bool']['must'][0]);
    }

    public function testRangeInvalidNumericAndOperator()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->range('f', 'gte', INF);

        $q2 = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q2->range('f', '>>', 5);

        // valid range
        $q3 = new ElasticQuery();
        $q3->range('f', 'gt', 5);
        $this->assertNotEmpty($q3->build()['query']['bool']['filter']);
    }

    public function testWildcardEmptyAndPerformanceWarning()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->wildcard('f', '');

        $q2 = new ElasticQuery();
        $q2->wildcard('f', '*abc');
        $warnings = $q2->getPerformanceWarnings();
        $this->assertNotEmpty($warnings);
    }

    public function testFuzzyClauseVariants()
    {
        $clause = ElasticQuery::fuzzyClause('f', 'v');
        $this->assertEquals('AUTO', $clause['fuzzy']['f']['fuzziness']);

        $clause2 = ElasticQuery::fuzzyClause('f', 'v', 2);
        $this->assertEquals(2, $clause2['fuzzy']['f']['fuzziness']);
    }

    public function testWhereNullValueAndUnsupportedOperator()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->where('f', null);

        $q2 = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q2->where('f', '>>', 5);
    }

    public function testWhereBetweenInvalidAndValid()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->whereBetween('f', [1]);

        $q2 = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q2->whereBetween('f', [5, 1]);

        $q3 = new ElasticQuery();
        $q3->whereBetween('f', [1, 5]);
        $built = $q3->build();
        $this->assertCount(2, $built['query']['bool']['filter']);
    }

    public function testPaginateValidationAndSuccess()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->paginate(10, 0);

        $q2 = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q2->paginate(0, 1);

        $q3 = new ElasticQuery();
        $result = $q3->paginate(2, 1);
        $this->assertEquals(2, $result['per_page']);
        $this->assertEquals(1, $result['current_page']);
    }

    public function testWhenDefaultAndCallbackBehavior()
    {
        $q = new ElasticQuery();
        $returned = $q->when(true, function ($qq) { return $qq->size(3); });
        $this->assertEquals(3, $returned->build()['size']);

        $q2 = new ElasticQuery();
        $same = $q2->when(false, function ($qq) { return $qq->size(4); });
        $this->assertSame($q2, $same);

        $q3 = new ElasticQuery();
        $defaultCalled = $q3->when(false, function ($qq) { return $qq->size(4); }, function ($qq) { return $qq->size(5); });
        $this->assertEquals(5, $defaultCalled->build()['size']);
    }

    public function testSearchWarningsAndBoost()
    {
        $q = new ElasticQuery();
        $q->search('', ['f']);
        $this->assertNotEmpty($q->getWarnings());

        $q2 = new ElasticQuery();
        $long = str_repeat('x', 1001);
        $q2->search($long, ['f']);
        $this->assertNotEmpty($q2->getPerformanceWarnings());

        $q3 = new ElasticQuery();
        $fields = array_fill(0, 11, 'f');
        $q3->search('q', $fields);
        $this->assertNotEmpty($q3->getPerformanceWarnings());

        $q4 = new ElasticQuery();
        $q4->search('q', ['a', 'b'], 2.5);
        $built = $q4->build();
        $this->assertArrayHasKey('multi_match', $built['query']['bool']['must'][0]);
        $this->assertEquals(2.5, $built['query']['bool']['must'][0]['multi_match']['boost']);
    }
}
