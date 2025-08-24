<?php

namespace Tests\Unit\ElasticQuery;

require_once __DIR__ . '/../Utility/TestableElasticQuery.php';

use PHPUnit\Framework\TestCase;

class ValidateConditionsDeepTest extends TestCase
{
    public function testValidateConditionValuesRecursesAndRejectsDeep()
    {
        $q = new TestableElasticQuery();

        $deep = [];
        $ref =& $deep;
        for ($i = 0; $i < 52; $i++) {
            $ref = [ 'v' => $ref ];
        }

        $this->expectException(\InvalidArgumentException::class);
        $q->callValidateConditionValues($deep, 0, 50);
    }

    public function testValidateConditionValuesValidatesTypes()
    {
        $q = new TestableElasticQuery();

        $cond = ['name' => 'alice', 'age' => 30];
        $q->callValidateConditionValues($cond);
        $this->assertTrue(true); // reached without exception
    }
}
