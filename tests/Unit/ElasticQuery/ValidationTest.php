<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryTopLevelValidationTest extends TestCase
{
    public function testRangeValidation()
    {
        $q = new ElasticQuery();
        $this->expectException(InvalidArgumentException::class);
        $q->range('price', 'invalid_op', 10);
    }

    public function testSizeAndFromLimits()
    {
        $q = new ElasticQuery();
        $q->size(5);
        $this->assertSame(5, $this->getProtected($q, 'query')['size']);

        $q->from(20);
        $this->assertSame(20, $this->getProtected($q, 'query')['from']);
    }

    private function getProtected($object, $prop)
    {
        $r = new ReflectionClass($object);
        $p = $r->getProperty('query');
        $p->setAccessible(true);
        return $p->getValue($object);
    }
}
