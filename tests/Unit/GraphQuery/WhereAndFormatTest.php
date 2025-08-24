<?php

namespace Tests\Unit\GraphQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;

class WhereAndFormatTest extends TestCase
{
    // public function testBuildWhereArgumentsAndFormat()
    // {
    //     $q = QueryCraft::graph();
    //     $ref = new \ReflectionClass($q);

    //     $m = $ref->getMethod('buildWhereArguments');
    //     $m->setAccessible(true);

    //     $where = [['field' => 'id', 'operator' => 'eq', 'value' => 1]];
    //     $args = $m->invoke($q, $where);
    //     $this->assertIsString($args);

    //     $fmt = $ref->getMethod('formatGraphQLValue');
    //     $fmt->setAccessible(true);
    //     $this->assertIsString($fmt->invoke($q, ['a' => 1]));
    //     $this->assertIsString($fmt->invoke($q, [1,2,3]));
    // }
}
