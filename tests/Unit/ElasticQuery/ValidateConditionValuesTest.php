<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ValidateConditionValuesTest extends TestCase
{
    public function testValidateConditionValuesRecursionLimitThrows()
    {
        $q = new ElasticQuery();
        $deep = [];
        $ref =& $deep;
        for ($i = 0; $i < 60; $i++) {
            $ref['level'] = [];
            $ref =& $ref['level'];
        }

        $m = new \ReflectionMethod(ElasticQuery::class, 'validateConditionValues');
        $m->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $m->invokeArgs($q, [$deep, 0, 50]);
    }
}
