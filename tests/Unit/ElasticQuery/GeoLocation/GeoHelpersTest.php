<?php

namespace Tests\Unit\ElasticQuery\GeoLocation;

require_once __DIR__ . '/../Utility/TestableElasticQuery.php';

use PHPUnit\Framework\TestCase;

class GeoHelpersTest extends TestCase
{
    public function testValidateGeoCoordinatesRejectsNonNumeric()
    {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->callValidateGeoCoordinates('north', 'east');
    }

    public function testGeoDistanceAcceptsArrayLocation()
    {
        $q = new TestableElasticQuery();
        $result = $q->geoDistance('loc', ['lat' => 10, 'lon' => 20], '5km');
        $this->assertInstanceOf(TestableElasticQuery::class, $result);
        $built = $q->build();
        $this->assertArrayHasKey('query', $built);
        $this->assertArrayHasKey('geo_distance', $built['query']['bool']['must'][0]);
    }
}
