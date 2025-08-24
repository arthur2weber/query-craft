<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class MongoQueryAdditionalCoverageTest extends TestCase
{
    public function testGroupVariantsProduceExpectedStages()
    {
        $q1 = new MongoQuery();
        $q1->group('category');
        $pipeline1 = $q1->build();
        // group('category') should produce a $group stage where _id => '$category'
        $this->assertTrue(in_array('$group', array_map('array_key_first', $pipeline1)));
        $groupStage = array_values(array_filter($pipeline1, fn($s) => array_key_first($s) === '$group'))[0];
        $this->assertArrayHasKey('_id', $groupStage['$group']);
        $this->assertEquals('$category', $groupStage['$group']['_id']);

        // pass an array with explicit _id
        $q2 = new MongoQuery();
        $q2->group(['_id' => '$type', 'count' => ['$sum' => 1]]);
        $pipeline2 = $q2->build();
        $this->assertTrue(in_array('$group', array_map('array_key_first', $pipeline2)));
        $groupStage2 = array_values(array_filter($pipeline2, fn($s) => array_key_first($s) === '$group'))[0];
        $this->assertEquals('$type', $groupStage2['$group']['_id']);
        $this->assertArrayHasKey('count', $groupStage2['$group']);

        // pass an array without _id (should wrap into _id)
        $q3 = new MongoQuery();
        $q3->group(['fieldA' => 1]);
        $pipeline3 = $q3->build();
        $this->assertTrue(in_array('$group', array_map('array_key_first', $pipeline3)));
        $groupStage3 = array_values(array_filter($pipeline3, fn($s) => array_key_first($s) === '$group'))[0];
        $this->assertArrayHasKey('_id', $groupStage3['$group']);
    }

    public function testProjectVariants()
    {
        $q = new MongoQuery();
        // associative
        $q->project(['a' => 1, 'b' => ['$concat' => ['$c', 'd']]]);
        $pipeline = $q->build();
        $this->assertTrue(in_array('$project', array_map('array_key_first', $pipeline)));

        // numeric list
        $q2 = new MongoQuery();
        $q2->project(['x', 'y']);
        $pipeline2 = $q2->build();
        $this->assertTrue(in_array('$project', array_map('array_key_first', $pipeline2)));

        // merging complex expressions via array entries
        $q3 = new MongoQuery();
        $q3->project([['z' => ['$add' => [1,2]]]]);
        $pipeline3 = $q3->build();
        $this->assertTrue(in_array('$project', array_map('array_key_first', $pipeline3)));
    }

    public function testAggregationFallbackAndTermsSize()
    {
        $q = new MongoQuery();
        // fallback stage (not sum/terms)
        $q->aggregation('custom', ['$addFields' => ['foo' => 'bar']]);
        $pipeline = $q->build();
        // Expect the custom stage present
        $this->assertTrue(is_array($pipeline));
        $found = false;
        foreach ($pipeline as $stage) {
            if (array_key_first($stage) === '$addFields') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected $addFields stage from fallback aggregation');

        // terms with size (ensure group + limit produced)
        $q2 = new MongoQuery();
        $q2->aggregation('terms', ['terms' => ['field' => 'category', 'size' => 1]]);
        $pipeline2 = $q2->build();
        $this->assertGreaterThanOrEqual(2, count($pipeline2));
        $this->assertEquals('$group', array_key_first($pipeline2[0]));
        $this->assertEquals('$limit', array_key_first($pipeline2[1]));
    }

    public function testBuildMongoConditionAdditionalOperators()
    {
        $q = new MongoQuery();
        $q->where('t_type', 'type', 'string')
          ->where('t_all', 'all', ['a','b'])
          ->where('t_elem', 'elemMatch', ['sub' => 1])
          ->where('t_mod', 'mod', [2,0])
          ->where('t_near', 'near', ['$geometry' => ['type' => 'Point','coordinates' => [0,0]]])
          ->where('t_geoWithin', 'geoWithin', ['$geometry' => ['type' => 'Polygon','coordinates' => [[[0,0],[1,0],[1,1],[0,0]]]]])
          ->where('t_geoIntersects', 'geoIntersects', ['$geometry' => ['type' => 'Point','coordinates' => [0,0]]]);

        $built = $q->build();

        $this->assertArrayHasKey('t_type', $built['filter']);
        $this->assertEquals(['$type' => 'string'], $built['filter']['t_type']);

        $this->assertArrayHasKey('t_all', $built['filter']);
        $this->assertEquals(['$all' => ['a','b']], $built['filter']['t_all']);

        $this->assertArrayHasKey('t_elem', $built['filter']);
        $this->assertEquals(['$elemMatch' => ['sub' => 1]], $built['filter']['t_elem']);

        $this->assertArrayHasKey('t_mod', $built['filter']);
        $this->assertEquals(['$mod' => [2,0]], $built['filter']['t_mod']);

        $this->assertArrayHasKey('t_near', $built['filter']);
        $this->assertEquals(['$near' => ['$geometry' => ['type' => 'Point','coordinates' => [0,0]]]], $built['filter']['t_near']);

        $this->assertArrayHasKey('t_geoWithin', $built['filter']);
        $this->assertArrayHasKey('$geoWithin', $built['filter']['t_geoWithin']);

        $this->assertArrayHasKey('t_geoIntersects', $built['filter']);
        $this->assertArrayHasKey('$geoIntersects', $built['filter']['t_geoIntersects']);
    }

    public function testTermClauseCacheEviction()
    {
        $ref = new ReflectionClass(MongoQuery::class);
        $propCache = $ref->getProperty('clauseCache');
        $propCache->setAccessible(true);
        $propSize = $ref->getProperty('cacheSize');
        $propSize->setAccessible(true);
        $propMax = $ref->getProperty('maxCacheSize');
        $propMax->setAccessible(true);

        // Reset cache properties for predictable behavior
        $propCache->setValue(null, []);
        $propSize->setValue(null, 0);
        $max = $propMax->getValue();

        // Insert more than maxCacheSize unique term clauses to trigger eviction logic
        $iterations = $max + 25;
        for ($i = 0; $i < $iterations; $i++) {
            MongoQuery::termClause('field_' . $i, $i);
        }

        $cache = $propCache->getValue();
        $size = $propSize->getValue();

        // After eviction, cache size should be <= maxCacheSize
        $this->assertLessThanOrEqual($max, $size);
        // The last inserted key should exist in cache
        $lastKey = 'term:field_' . ($iterations - 1) . ':' . (string)($iterations - 1);
        $this->assertTrue(array_key_exists($lastKey, $cache));
    }
}
