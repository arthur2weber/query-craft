<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class MongoQueryBranchCoverageTest extends TestCase
{
    public function testBuildMongoPipelineReturnsAggregationWhenUseAggregationTrue()
    {
        $q = new MongoQuery();
        // this will set useAggregation = true and add an aggregation stage
        $q->search('term');

        $ref = new ReflectionClass(MongoQuery::class);
        $m = $ref->getMethod('buildMongoPipeline');
        $m->setAccessible(true);

        $result = $m->invoke($q);

        $this->assertIsArray($result);
        // should contain a $match stage coming from the text search
        $this->assertTrue(in_array('$match', array_map('array_key_first', $result)));
    }

    public function testBuildMongoFilterMergesTopLevelOperatorArrays()
    {
        $q = new MongoQuery();
        // both will produce a top-level $or operator when used as default (boolean = 'and')
        $q->whereNotBetween('a', [1, 2]);
        $q->whereNotBetween('b', [3, 4]);

        $built = $q->build();
        $this->assertArrayHasKey('filter', $built);
        $filter = $built['filter'];

        // After merging, $or should exist and be an array
        $this->assertArrayHasKey('$or', $filter);
        $this->assertIsArray($filter['$or']);
        $this->assertNotEmpty($filter['$or']);
    }

    public function testBuildMongoFilterMergesFieldLevelArrays()
    {
        $q = new MongoQuery();
        // two comparisons on the same field should merge into a single field with multiple operators
        $q->where('score', '>', 10)
          ->where('score', '<', 100);

        $built = $q->build();
        $this->assertArrayHasKey('filter', $built);
        $filter = $built['filter'];

        $this->assertArrayHasKey('score', $filter);
        $this->assertIsArray($filter['score']);
        $this->assertArrayHasKey('$gt', $filter['score']);
        $this->assertArrayHasKey('$lt', $filter['score']);
        $this->assertEquals(10, $filter['score']['$gt']);
        $this->assertEquals(100, $filter['score']['$lt']);
    }

    public function testNearLegacyGeometryAndWithinLegacyGeometryBranches()
    {
        // near() legacy geometry (no type/coordinates)
        $q = new MongoQuery();
        $q->near('loc', [10, 20], ['maxDistance' => 500]);
        $pipeline = $q->build();

        $matchStages = array_values(array_filter($pipeline, fn($s) => array_key_first($s) === '$match'));
        $this->assertNotEmpty($matchStages);
        $match = $matchStages[0]['$match'];

        $this->assertArrayHasKey('loc', $match);
        $this->assertArrayHasKey('$near', $match['loc']);
        $this->assertArrayHasKey('$maxDistance', $match['loc']['$near']);

        // within() legacy geometry branch (no type/coordinates)
        $q2 = new MongoQuery();
        $legacyShape = ['box' => [[0,0],[1,1]]];
        $q2->within('loc', $legacyShape);
        $pipeline2 = $q2->build();

        $matchStages2 = array_values(array_filter($pipeline2, fn($s) => array_key_first($s) === '$match'));
        $this->assertNotEmpty($matchStages2);
        $match2 = $matchStages2[0]['$match'];

        $this->assertArrayHasKey('loc', $match2);
        $this->assertArrayHasKey('$geoWithin', $match2['loc']);
        $this->assertArrayHasKey('$geometry', $match2['loc']['$geoWithin']);
    }

    public function testBuildMongoFilterDirectlyExercisesTopLevelOperatorFallback()
    {
        $q = new MongoQuery();

        // prepare where clauses that produce a top-level operator ($or)
        $wheres = [
            ['field' => '$or', 'operator' => '=', 'value' => 'scalar', 'type' => 'where', 'boolean' => 'and'],
            ['field' => '$or', 'operator' => '=', 'value' => ['array-value'], 'type' => 'where', 'boolean' => 'and'],
        ];

        $ref = new \ReflectionClass(MongoQuery::class);
        $rp = $ref->getProperty('wheres');
        $rp->setAccessible(true);
        // set protected property using two-argument form (avoid deprecation)
        $rp->setValue($q, $wheres);

        // call the protected buildMongoFilter() directly so coverage attributes exactly to that method
        $m = $ref->getMethod('buildMongoFilter');
        $m->setAccessible(true);
        $filter = $m->invoke($q);

        $this->assertArrayHasKey('$or', $filter);
        $this->assertSame(['array-value'], $filter['$or']);
    }
}
