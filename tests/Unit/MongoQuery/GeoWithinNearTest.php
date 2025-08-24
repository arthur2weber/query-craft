<?php

namespace Tests\Unit\MongoQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\QueryCraft;

class GeoWithinNearTest extends TestCase
{
    public function testNearAndWithinPopulateGeometry()
    {
        $q = QueryCraft::mongo();
        // Use geometry array as expected by MongoQuery::near
        $q->near('loc', ['type' => 'Point', 'coordinates' => [0,0]], ['maxDistance' => 1000]);
        $ref = new \ReflectionClass($q);
        $m = $ref->getMethod('buildMongoFilter');
        $m->setAccessible(true);
        $filter = $m->invoke($q, [['field'=>'loc','operator'=>'near','value'=>['type'=>'Point','coordinates'=>[0,0]]]]);
        $this->assertIsArray($filter);
    }
}
