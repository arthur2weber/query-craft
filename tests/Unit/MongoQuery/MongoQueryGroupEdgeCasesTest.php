<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class MongoQueryGroupEdgeCasesTest extends TestCase
{
    public function testGroupWithNonStringNonArrayUsesValueAsId()
    {
        $q = new MongoQuery();
        $q->group(123);
        $pipeline = $q->build();

        $this->assertTrue(is_array($pipeline));
        $groupStages = array_values(array_filter($pipeline, fn($s) => array_key_first($s) === '$group'));
        $this->assertNotEmpty($groupStages);
        $groupStage = $groupStages[0]['$group'];
        $this->assertArrayHasKey('_id', $groupStage);
        $this->assertEquals(123, $groupStage['_id']);
    }

    public function testGroupWithNumericArrayBecomesIdArray()
    {
        $q = new MongoQuery();
        $q->group(['a', 'b']);
        $pipeline = $q->build();

        $groupStages = array_values(array_filter($pipeline, fn($s) => array_key_first($s) === '$group'));
        $this->assertNotEmpty($groupStages);
        $groupStage = $groupStages[0]['$group'];
        $this->assertArrayHasKey('_id', $groupStage);
        $this->assertIsArray($groupStage['_id']);
        $this->assertEquals(['a','b'], $groupStage['_id']);
    }

    public function testGroupMergesOperationsWhenProvided()
    {
        $q = new MongoQuery();
        $q->group('category', ['count' => ['$sum' => 1]]);
        $pipeline = $q->build();

        $groupStages = array_values(array_filter($pipeline, fn($s) => array_key_first($s) === '$group'));
        $this->assertNotEmpty($groupStages);
        $groupStage = $groupStages[0]['$group'];

        $this->assertArrayHasKey('_id', $groupStage);
        $this->assertEquals('$category', $groupStage['_id']);
        $this->assertArrayHasKey('count', $groupStage);
        $this->assertEquals(['$sum' => 1], $groupStage['count']);
    }

    public function testGroupWithArrayIdAndOperationsMerge()
    {
        $q = new MongoQuery();
        $q->group(['_id' => '$type'], ['total' => ['$sum' => '$amount']]);
        $pipeline = $q->build();

        $groupStages = array_values(array_filter($pipeline, fn($s) => array_key_first($s) === '$group'));
        $this->assertNotEmpty($groupStages);
        $groupStage = $groupStages[0]['$group'];

        $this->assertArrayHasKey('_id', $groupStage);
        $this->assertEquals('$type', $groupStage['_id']);
        $this->assertArrayHasKey('total', $groupStage);
        $this->assertEquals(['$sum' => '$amount'], $groupStage['total']);
    }
}
