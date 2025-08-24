<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class BuildMongoConditionAndFilterBranchesTest extends TestCase
{
    public function testBuildMongoConditionManyOperators()
    {
        $ops = [
            ['field' => 'f_eq', 'op' => '=', 'val' => 10, 'expected' => 10],
            ['field' => 'f_ne', 'op' => '!=', 'val' => 3, 'expected' => ['$ne' => 3]],
            ['field' => 'f_gt', 'op' => '>', 'val' => 5, 'expected' => ['$gt' => 5]],
            ['field' => 'f_gte', 'op' => '>=', 'val' => 6, 'expected' => ['$gte' => 6]],
            ['field' => 'f_lt', 'op' => '<', 'val' => 2, 'expected' => ['$lt' => 2]],
            ['field' => 'f_lte', 'op' => '<=', 'val' => 4, 'expected' => ['$lte' => 4]],
            ['field' => 'f_in', 'op' => 'in', 'val' => [1,2], 'expected' => ['$in' => [1,2]]],
            ['field' => 'f_not_in', 'op' => 'not_in', 'val' => [3,4], 'expected' => ['$nin' => [3,4]]],
            ['field' => 'f_between', 'op' => 'between', 'val' => [7,9], 'expected' => ['$gte' => 7, '$lte' => 9]],
            // not_between yields top-level $or
            ['field' => 'f_not_between', 'op' => 'not_between', 'val' => [1,2], 'expected_top_key' => '$or'],
            ['field' => 'f_null', 'op' => 'null', 'val' => null, 'expected' => null],
            ['field' => 'f_not_null', 'op' => 'not_null', 'val' => null, 'expected' => ['$ne' => null]],
            ['field' => 'f_like', 'op' => 'like', 'val' => '%abc%', 'expected' => ['$regex' => '.*abc.*', '$options' => 'i']],
            ['field' => 'f_regex', 'op' => 'regex', 'val' => '^start', 'expected' => ['$regex' => '^start']],
            ['field' => 'f_size', 'op' => 'size', 'val' => 2, 'expected' => ['$size' => 2]],
            ['field' => 'f_exists', 'op' => 'exists', 'val' => true, 'expected' => ['$exists' => true]],
            ['field' => 'f_type', 'op' => 'type', 'val' => 'string', 'expected' => ['$type' => 'string']],
            ['field' => 'f_all', 'op' => 'all', 'val' => ['a','b'], 'expected' => ['$all' => ['a','b']]],
            ['field' => 'f_elem', 'op' => 'elemMatch', 'val' => ['s' => 1], 'expected' => ['$elemMatch' => ['s' => 1]]],
            ['field' => 'f_mod', 'op' => 'mod', 'val' => [2,0], 'expected' => ['$mod' => [2,0]]],
            ['field' => 'f_near', 'op' => 'near', 'val' => ['$geometry' => ['type' => 'Point', 'coordinates' => [0,0]]], 'expected' => ['$near' => ['$geometry' => ['type' => 'Point', 'coordinates' => [0,0]]]]],
            ['field' => 'f_geoWithin', 'op' => 'geoWithin', 'val' => ['$geometry' => ['type' => 'Polygon','coordinates' => [[[0,0],[1,0],[1,1],[0,0]]]]], 'expected_key' => 'f_geoWithin'],
            ['field' => 'f_geoIntersects', 'op' => 'geoIntersects', 'val' => ['$geometry' => ['type' => 'Point','coordinates' => [0,0]]], 'expected_key' => 'f_geoIntersects'],
        ];

        $q = new MongoQuery();
        foreach ($ops as $op) {
            $q->where($op['field'], $op['op'], $op['val']);
        }

        $built = $q->build();
        $filter = $built['filter'];

        foreach ($ops as $op) {
            if (isset($op['expected_top_key'])) {
                $this->assertArrayHasKey($op['expected_top_key'], $filter);
            } elseif (isset($op['expected_key'])) {
                $this->assertArrayHasKey($op['expected_key'], $filter);
            } else {
                $this->assertArrayHasKey($op['field'], $filter);
                $this->assertEquals($op['expected'], $filter[$op['field']]);
            }
        }
    }

    public function testBuildMongoFilterTopLevelOrMergesWhenMultipleTopLevelOrsAdded()
    {
        $q = new MongoQuery();
        // not_between produces a top-level $or element
        $q->where('a', 'not_between', [1,3]);
        $q->where('b', 'not_between', [2,4]);

        $built = $q->build();
        $this->assertArrayHasKey('filter', $built);
        $filter = $built['filter'];

        $this->assertArrayHasKey('$or', $filter);
        $this->assertIsArray($filter['$or']);
        // Each not_between produces two sub-clauses; two such calls -> 4 merged clauses
        $this->assertCount(4, $filter['$or']);
    }

    public function testBuildMongoFilterFieldLevelMergesScalarAndArrayOperators()
    {
        $q = new MongoQuery();
        $q->where('a', 1);
        $q->where('a', 'in', [1,2]);

        $built = $q->build();
        $this->assertArrayHasKey('filter', $built);
        $this->assertArrayHasKey('a', $built['filter']);
        $this->assertArrayHasKey('$eq', $built['filter']['a']);
        $this->assertArrayHasKey('$in', $built['filter']['a']);
        $this->assertEquals(1, $built['filter']['a']['$eq']);
    }

    public function testBuildMongoFilterFallbackOverwriteWithDefaultOperator()
    {
        $q = new MongoQuery();
        $q->where('a', 'unknown', 'first');
        $q->where('a', 'unknown', 'second');

        $built = $q->build();
        $this->assertEquals('second', $built['filter']['a']);
    }

    public function testOrWhereAloneProducesTopLevelOr()
    {
        $q = new MongoQuery();
        $q->orWhere('x', 5);
        $built = $q->build();
        $this->assertArrayHasKey('filter', $built);
        $this->assertArrayHasKey('$or', $built['filter']);
        $this->assertEquals([['x' => 5]], $built['filter']['$or']);
    }
}
