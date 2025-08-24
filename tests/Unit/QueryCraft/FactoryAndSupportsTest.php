<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;

class FactoryAndSupportsTest extends TestCase
{
    public function testForThrowsOnUnsupportedType()
    {
        $this->expectException(\InvalidArgumentException::class);
        QueryCraft::for('unsupported_type');
    }

    public function testSupportsReturnsFalseForUnknownType()
    {
        $this->assertFalse(QueryCraft::supports('unknown_type'));
    }
}
