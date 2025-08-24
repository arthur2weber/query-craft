<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class MongoQueryTopLevelAggregationTest extends TestCase
{
    public function testLookupAndBuildAggregation()
    {
        $q = new MongoQuery();
        $q->lookup('profiles', '_id', 'user_id', 'profile');
        $pipeline = $q->build();
        $this->assertIsArray($pipeline);
        $this->assertNotEmpty($pipeline);
    }

    public function testProjectAndSelect()
    {
        $q = new MongoQuery();
        $q->select(['id','name']);
        $this->assertSame(['id','name'], $this->getProtected($q, 'selects'));
    }

    private function getProtected($object, $prop)
    {
        $r = new ReflectionClass($object);
        $p = $r->getProperty($prop);
        $p->setAccessible(true);
        return $p->getValue($object);
    }
}
