<?php

namespace Arthur2weber\QueryCraft\Tests\Unit\ElasticQuery;

use Arthur2weber\QueryCraft\Query\ElasticQuery;
use PHPUnit\Framework\TestCase;

class GeoBoundingBoxVariantsTest extends TestCase
{
    public function testGeoBoundingBoxWithArrayPoints()
    {
        $q = new ElasticQuery();
        $q->must(['geo_bounding_box' => [
            'location' => [
                'top_left' => ['lat' => 45, 'lon' => -10],
                'bottom_right' => ['lat' => 40, 'lon' => -5]
            ]
        ]]);
        $b = $q->build();
        $this->assertArrayHasKey('query', $b);
    }

    public function testGeoBoundingBoxWithInvalidFormatThrows()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        // lat out of range
        $q->must(['geo_bounding_box' => [
            'location' => [
                'top_left' => ['lat' => -200, 'lon' => 10],
                'bottom_right' => ['lat' => 40, 'lon' => 10]
            ]
        ]]);
    }
}
