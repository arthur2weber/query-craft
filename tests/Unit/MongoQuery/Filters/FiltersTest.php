<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class MongoQueryFiltersTest extends TestCase
{
    public function testWhereAndWhereInAndBetween()
    {
        $q = new MongoQuery();
        $q->where('age', '>=', 18);
        $q->whereIn('tags', ['a','b']);
        $this->assertNotEmpty($this->getProtected($q, 'wheres'));

        $this->expectException(InvalidArgumentException::class);
        $q->whereBetween('age', [5]);
    }

    private function getProtected($object, $prop)
    {
        $r = new ReflectionClass($object);
        $p = $r->getProperty($prop);
        $p->setAccessible(true);
        return $p->getValue($object);
    }
}
