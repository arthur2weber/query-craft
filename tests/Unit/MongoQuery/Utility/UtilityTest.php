<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class MongoQueryUtilityTest extends TestCase
{
    public function testBuildFindQueryReturnsProjectionWhenSelectSet()
    {
        $q = new MongoQuery();
        $q->select(['id','name']);
        $built = $q->build();
        $this->assertArrayHasKey('options', $built);
        $this->assertArrayHasKey('projection', $built['options']);
    }

    public function testWhenUnlessCallbacks()
    {
        $q = new MongoQuery();
        $q->when(true, function($q) { $q->select(['a']); });
        $this->assertSame(['a'], $this->getProtected($q, 'selects'));
        $q->unless(false, function($q) { $q->select(['b']); });
        $this->assertSame(['b'], $this->getProtected($q, 'selects'));
    }

    private function getProtected($object, $prop)
    {
        $r = new ReflectionClass($object);
        $p = $r->getProperty($prop);
        $p->setAccessible(true);
        return $p->getValue($object);
    }
}
