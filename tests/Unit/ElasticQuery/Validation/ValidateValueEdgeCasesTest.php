<?php

namespace Arthur2weber\QueryCraft\Tests\Unit\ElasticQuery;

use Arthur2weber\QueryCraft\Query\ElasticQuery;
use PHPUnit\Framework\TestCase;

class ValidateValueEdgeCasesTest extends TestCase
{
    public function testWhereRejectsCallableValue()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->where('field', '=', function () { return 'x'; });
    }

    public function testMatchRejectsEmptyStringWarningIsTriggeredButAllowed()
    {
        $q = new ElasticQuery();
        // match on empty string triggers a user warning but still builds a clause
        set_error_handler(function () { return true; }, E_USER_WARNING);
        try {
            $q->match('title', '');
        } finally {
            restore_error_handler();
        }

        $built = $q->build();
        $this->assertArrayHasKey('query', $built);
        $this->assertNotEmpty($built['query']['bool']['must']);
    }
}
