<?php

namespace Tests\Unit\BaseQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;

class ValidateHelpersTest extends TestCase
{
    public function testValidateNonEmptyArrayAndFieldName()
    {
        $q = QueryCraft::elastic();
        $ref = new \ReflectionClass($q);

        $m = $ref->getMethod('validateNonEmptyArray');
        $m->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $m->invoke($q, []);
    }
}
