<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryTargetedUncoveredTest extends TestCase
{
    public function testAggregationEmptyThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $q = new ElasticQuery();
        // empty aggregation configuration should throw
        $q->aggregation('myagg', []);
    }

    public function testGeoDistanceLongitudeThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $q = new ElasticQuery();
        // lon out of range should trigger validateGeoCoordinates -> exception
        $q->geoDistance('loc', ['lat' => 10, 'lon' => 200], '5km');
    }

    public function testRangeAddsFilterAndVerboseLog()
    {
        $q = new ElasticQuery();
        // enable verbose to ensure addVerboseLog path is exercised
        $q->verbose(true);
        $q->range('age', 'gt', 30);
        $built = $q->build();
        $this->assertArrayHasKey('filter', $built['query']['bool']);
        $this->assertNotEmpty($built['query']['bool']['filter']);
        $this->assertNotEmpty($q->getVerboseLog());
    }

    public function testWhereValidateValueAndFilterOperator()
    {
        $q = new ElasticQuery();
        $q->where('status', '=', 'active');
        $built = $q->build();
        $this->assertArrayHasKey('filter', $built['query']['bool']);
        $this->assertNotEmpty($built['query']['bool']['filter']);
    }

    public function testWhereBetweenInvalidAndValid()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->whereBetween('f', [1]);

        // min > max should throw
        $q2 = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q2->whereBetween('f', [5, 1]);

        // valid case
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

    public function testWhereNotEqualsCreatesMustNotTermLater()
    {
        $q = new ElasticQuery();
        $q->where('age', '!=', 30);
        $built = $q->build();
        $this->assertArrayHasKey('must_not', $built['query']['bool']);
        $this->assertContainsEquals(['term' => ['age' => 30]], $built['query']['bool']['must_not']);
    }

    public function testWhereBetweenSuccessProvidesTwoFiltersLater()
    {
        $q = new ElasticQuery();
        $q->whereBetween('score', [0, 100]);
        $built = $q->build();
        $this->assertArrayHasKey('filter', $built['query']['bool']);
        $this->assertCount(2, $built['query']['bool']['filter']);
        $this->assertEquals(['range' => ['score' => ['gte' => 0]]], $built['query']['bool']['filter'][0]);
        $this->assertEquals(['range' => ['score' => ['lte' => 100]]], $built['query']['bool']['filter'][1]);
    }

    public function testShouldSetsMinimumShouldMatch()
    {
        $q = new ElasticQuery();
        $q->should(['term' => ['a' => 'b']], 2);
        $built = $q->build();
        $this->assertArrayHasKey('minimum_should_match', $built['query']['bool']);
        $this->assertEquals(2, $built['query']['bool']['minimum_should_match']);
    }

    public function testWhereBetweenMinControlCharThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $q = new ElasticQuery();
        // min contains control character, validateValue should throw
        $q->whereBetween('f', ["\x01", 5]);
    }

    public function testWhereBetweenMaxControlCharThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $q = new ElasticQuery();
        // max contains control character, validateValue should throw
        $q->whereBetween('f', [1, "\x01"]);
    }

    public function testPaginatePerPageLessThanOneThrowsExplicit()
    {
        $this->expectException(\InvalidArgumentException::class);
        $q = new ElasticQuery();
        // perPage < 1 should throw
        $q->paginate(0, 1);
    }
}
