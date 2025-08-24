<?php

namespace Tests\Unit\ElasticQuery\Geo;

require_once __DIR__ . '/../TestableElasticQuery.php';

use PHPUnit\Framework\TestCase;

class GeoHelpersTest extends TestCase
{
    private function makeTestable()
    {
        return new \Tests\Unit\ElasticQuery\TestableElasticQuery();
    }

    public function testValidateGeoCoordinatesRejectsInvalid()
    {
        $q = $this->makeTestable();

        $this->expectException(\InvalidArgumentException::class);
        $q->callValidateGeoCoordinates('not-numeric', 'also');

        try {
            $q->callValidateGeoCoordinates(-91, 0);
            $this->fail('Expected exception for latitude out of range');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('Latitude must be between -90 and 90', $e->getMessage());
        }

        $this->expectException(\InvalidArgumentException::class);
        $q->callValidateGeoCoordinates(0, 181);
    }

    public function testGeoDistanceValidAndInvalidFormats()
    {
        $q = $this->makeTestable();

        $this->expectException(\InvalidArgumentException::class);
        $q->geoDistance('loc', '12.3,-45.6', '100');

        $this->expectException(\InvalidArgumentException::class);
        $q->geoDistance('loc', ['lat' => 1], '5km');

        $res = $q->geoDistance('location', '12.34,-56.78', '5km');
        $this->assertInstanceOf(\Arthur2weber\QueryCraft\Query\ElasticQuery::class, $res);
    }
}
