<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class TimeoutAndScriptValidationTest extends TestCase
{
    public function testTimeoutInvalidFormatThrows()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->timeout('30seconds');
    }

    public function testValidateScriptEmptyThrows()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->scriptScore('   ');
    }
}
