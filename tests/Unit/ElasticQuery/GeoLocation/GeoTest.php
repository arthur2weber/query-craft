<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryGeoTest extends TestCase
{
    public function testGeoDistanceValidation()
    {
        $q = new ElasticQuery();
        $this->expectException(InvalidArgumentException::class);
        $q->geoDistance('location','invalid','5km');
    }

    public function testGeoDistanceArrayAcceptsValidCoords()
    {
        $q = new ElasticQuery();
        $q->geoDistance('loc',['lat'=>40,'lon'=>-70],'5km');
        $built = $q->build();
        $this->assertArrayHasKey('geo_distance', $built['query']['bool']['must'][0]);
    }
}
