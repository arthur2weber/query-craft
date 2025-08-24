<?php

namespace Tests\Unit\BaseQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;

class BaseQueryExceptionBranchesTest extends TestCase
{
    public function testOrderByInvalidDirectionThrows()
    {
        $q = QueryCraft::elastic();
        $this->expectException(\InvalidArgumentException::class);
        $q->orderBy('field', 'sideways');
    }

    public function testTakeNegativeThrows()
    {
        $q = QueryCraft::elastic();
        $this->expectException(\InvalidArgumentException::class);
        $q->take(-5);
    }

    public function testSkipNegativeThrows()
    {
        $q = QueryCraft::elastic();
        $this->expectException(\InvalidArgumentException::class);
        $q->skip(-2);
    }

    public function testSelectNonStringFieldThrows()
    {
        $q = QueryCraft::elastic();
        $this->expectException(\InvalidArgumentException::class);
        $q->select([123]);
    }
}
