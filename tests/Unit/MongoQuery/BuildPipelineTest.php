<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;

class BuildPipelineTest extends TestCase
{
    public function testBuildMongoPipelineReturnsArray()
    {
        $q = QueryCraft::mongo();
        $ref = new \ReflectionClass($q);
        $m = $ref->getMethod('buildMongoPipeline');
        $m->setAccessible(true);

        $pipeline = $m->invoke($q);
        $this->assertIsArray($pipeline);
    }
}
