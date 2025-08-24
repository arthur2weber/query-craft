<?php

namespace Tests\Unit\ElasticQuery\Filters;

require_once __DIR__ . '/../Utility/TestableElasticQuery.php';

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ScriptScoreTest extends TestCase
{
    public function testScriptScoreWrapsQuery()
    {
        $q = new ElasticQuery();
        $q->filter('status', 'active');
        $q->scriptScore("return 1;");
        $built = $q->build();
        $this->assertArrayHasKey('script_score', $built['query']);
    }
}
