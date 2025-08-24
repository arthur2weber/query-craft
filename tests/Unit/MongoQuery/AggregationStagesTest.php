<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;

class AggregationStagesTest extends TestCase
{
    public function testAggregationStagesContainSortAndLimit()
    {
        $q = QueryCraft::mongo();
        // call aggregation with (name, definition) per MongoQuery::aggregation signature
        $q->aggregation('count', ['sum' => ['field' => 'x']]);
        $ref = new \ReflectionClass($q);
        $m = $ref->getMethod('buildAggregationPipeline');
        $m->setAccessible(true);
        $pipe = $m->invoke($q);
        $this->assertIsArray($pipe);
        $this->assertNotEmpty($pipe);
    }
}
