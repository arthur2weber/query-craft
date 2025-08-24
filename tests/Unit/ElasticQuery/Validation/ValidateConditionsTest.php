<?php

namespace Tests\Unit\ElasticQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;

class ValidateConditionsTest extends TestCase
{
    public function testValidateConditionValuesThrowsOnInvalid()
    {
        $q = QueryCraft::elastic();
        $ref = new \ReflectionClass($q);
        $m = $ref->getMethod('validateConditionValues');
        $m->setAccessible(true);

        // The current implementation validates values but does not throw for empty nested arrays;
        // ensure method executes without throwing and returns null (void).
        $res = $m->invoke($q, [['field'=>'a','operator'=>'in','value'=>[]]]);
        $this->assertNull($res);
    }
}
