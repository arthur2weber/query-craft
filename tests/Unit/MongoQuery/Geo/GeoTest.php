<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class MongoQueryGeoTest extends TestCase
{
    public function testGeoDistanceCreatesGeoNearStage()
    {
        $q = new MongoQuery();
        $q->geoDistance('location', '10,20', '5km');
        $pipeline = $q->build();
        $found = false;
        foreach ($pipeline as $stage) {
            if (is_array($stage) && array_key_first($stage) === '$geoNear') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected $geoNear stage in pipeline');
    }
}
