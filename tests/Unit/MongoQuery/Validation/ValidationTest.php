<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class MongoQueryValidationTest extends TestCase
{
    public function testBuildMongoConditionOperators()
    {
        $q = new MongoQuery();
        $method = new ReflectionClass($q);
        $p = $method->getMethod('buildMongoCondition');
        $p->setAccessible(true);
        $res = $p->invokeArgs($q, ['age', '!=', 30]);
        $this->assertArrayHasKey('age', $res);
        $this->assertArrayHasKey('$ne', $res['age']);
    }

    public function testBuildMongoSort()
    {
        $q = new MongoQuery();
        $q->orderBy('name','desc');
        $sort = $this->invokeProtectedMethod($q, 'buildMongoSort');
        $this->assertSame(['name' => -1], $sort);
    }

    private function invokeProtectedMethod($object, $method, $args = [])
    {
        $r = new ReflectionClass($object);
        $m = $r->getMethod($method);
        $m->setAccessible(true);
        return $m->invokeArgs($object, $args);
    }
}
