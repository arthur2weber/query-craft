<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;

class BuildFilterConditionTest extends TestCase
{
    public function testBuildMongoFilterAndConditions()
    {
        $q = QueryCraft::mongo();
        $ref = new \ReflectionClass($q);

        $filterMethod = $ref->getMethod('buildMongoFilter');
        $filterMethod->setAccessible(true);

        $conditions = [
            ['field' => 'age', 'operator' => 'in', 'value' => [1,2,3]],
            ['field' => 'name', 'operator' => 'regex', 'value' => 'Jo.*'],
            ['field' => 'loc', 'operator' => 'near', 'value' => ['lat' => 0, 'lon' => 0, 'distance' => 1000]],
        ];

        $filter = $filterMethod->invoke($q, $conditions);
        $this->assertIsArray($filter);
    }
}
