<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class MongoQueryTargetedTest extends TestCase
{
    public function testBuildFindQueryWithWheresSortPaginationAndProjection()
    {
        $q = new MongoQuery();
        $q->where('a', 1)
          ->where('b', '>', 2)
          ->orderBy('c', 'desc')
          ->take(5)
          ->skip(2)
          ->select(['x', 'y']);

        $built = $q->build();

        $this->assertArrayHasKey('filter', $built);
        $this->assertEquals(1, $built['filter']['a']);
        $this->assertEquals(['$gt' => 2], $built['filter']['b']);

        $this->assertArrayHasKey('options', $built);
        $this->assertArrayHasKey('sort', $built['options']);
        $this->assertEquals(['c' => -1], $built['options']['sort']);
        $this->assertEquals(5, $built['options']['limit']);
        $this->assertEquals(2, $built['options']['skip']);
        $this->assertEquals(['x' => 1, 'y' => 1], $built['options']['projection']);
    }

    public function testOrWhereCombinesWithExistingFilterUsingAndOrStructure()
    {
        $q = new MongoQuery();
        $q->where('a', 1)
          ->orWhere('b', 2);

        $built = $q->build();

        // Expect an $and with the original filter and an $or array
        $this->assertArrayHasKey('$and', $built['filter']);
        $and = $built['filter']['$and'];
        $this->assertCount(2, $and);
        $this->assertEquals(['a' => 1], $and[0]);
        $this->assertArrayHasKey('$or', $and[1]);
        $this->assertEquals([['b' => 2]], $and[1]['$or']);
    }

    public function testWhereNotBetweenProducesTopLevelOr()
    {
        $q = new MongoQuery();
        $q->whereNotBetween('n', [1, 5]);

        $built = $q->build();

        $this->assertArrayHasKey('$or', $built['filter']);
        $this->assertEquals(
            [['n' => ['$lt' => 1]], ['n' => ['$gt' => 5]]],
            $built['filter']['$or']
        );
    }

    public function testMultipleOperatorsMergeOnSameField()
    {
        $q = new MongoQuery();
        $q->where('a', 1)
          ->where('a', '>', 2);

        $built = $q->build();

        $this->assertArrayHasKey('a', $built['filter']);
        $this->assertArrayHasKey('$eq', $built['filter']['a']);
        $this->assertArrayHasKey('$gt', $built['filter']['a']);
        $this->assertEquals(1, $built['filter']['a']['$eq']);
        $this->assertEquals(2, $built['filter']['a']['$gt']);
    }

    public function testBuildMongoConditionVariousOperators()
    {
        $q = new MongoQuery();
        $q->where('f', '!=', 3)
          ->where('g', 'in', [1,2,3])
          ->where('h', 'not_in', [4,5])
          ->where('i', 'between', [10,20])
          ->where('j', 'null')
          ->where('k', 'not_null')
          ->where('l', 'like', '%abc%')
          ->where('m', 'regex', '^start')
          ->where('p', 'size', 2)
          ->where('q', 'exists', true);

        $built = $q->build();

        $this->assertEquals(['$ne' => 3], $built['filter']['f']);
        $this->assertEquals(['$in' => [1,2,3]], $built['filter']['g']);
        $this->assertEquals(['$nin' => [4,5]], $built['filter']['h']);
        $this->assertEquals(['$gte' => 10, '$lte' => 20], $built['filter']['i']);
        $this->assertArrayHasKey('j', $built['filter']);
        $this->assertNull($built['filter']['j']);
        $this->assertEquals(['$ne' => null], $built['filter']['k']);
        $this->assertEquals(['$regex' => '.*abc.*', '$options' => 'i'], $built['filter']['l']);
        $this->assertEquals(['$regex' => '^start'], $built['filter']['m']);
        $this->assertEquals(['$size' => 2], $built['filter']['p']);
        $this->assertEquals(['$exists' => true], $built['filter']['q']);
    }

    public function testAggregationSumAndTermsWithSizeGeneratesExpectedPipeline()
    {
        $q = new MongoQuery();
        $q->aggregation('total', ['sum' => ['field' => 'price']]);
        $built = $q->build();

        $this->assertIsArray($built);
        $this->assertEquals(['$group' => ['_id' => null, 'total' => ['$sum' => '$price']]], $built[0]);

        $q2 = new MongoQuery();
        $q2->aggregation('cats', ['terms' => ['field' => 'category', 'size' => 2]]);
        $built2 = $q2->build();
        // Expect group then limit
        $this->assertEquals('$group', array_key_first($built2[0]));
        $this->assertEquals('$limit', array_key_first($built2[1]));
        $this->assertEquals(2, $built2[1]['$limit']);
    }

    public function testBuildAggregationPipelineAddsSortSkipLimitIfNotPresent()
    {
        $q = new MongoQuery();
        $q->where('x', 5);
        // Force aggregation mode by adding a group stage
        $q->group('x');
        $q->orderBy('x', 'asc');
        $q->take(10);
        $q->skip(3);

        $pipeline = $q->build();

        // First stage should be $match
        $this->assertEquals('$match', array_key_first($pipeline[0]));
        // pipeline contains group stage as added
        $this->assertTrue(in_array('$group', array_map('array_key_first', $pipeline)));
        // pipeline should include a $sort, $skip and $limit appended
        $keys = array_map('array_key_first', $pipeline);
        $this->assertContains('$sort', $keys);
        $this->assertContains('$skip', $keys);
        $this->assertContains('$limit', $keys);
    }

    public function testNearAndWithinAddGeoStages()
    {
        $q = new MongoQuery();
        $geojson = ['type' => 'Point', 'coordinates' => [40, -70]];
        $q->near('loc', $geojson, ['maxDistance' => 1000]);
        $built = $q->build();
        // because near uses match() internally, expect $match stage in pipeline
        $this->assertEquals('$match', array_key_first($built[0]));

        $q2 = new MongoQuery();
        $q2->within('loc', $geojson);
        $built2 = $q2->build();
        $this->assertEquals('$match', array_key_first($built2[0]));
    }
}
