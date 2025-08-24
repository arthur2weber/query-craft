<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery;

class ElasticQueryAddSortTest extends TestCase
{
    public function testAddSortAndOrderAliases()
    {
        $q = new ElasticQuery();
        $q->addSort([
            '_geo_distance' => [
                'location' => '40.7128,-74.0060',
                'order' => 'asc',
                'unit' => 'km'
            ]
        ])
        ->orderBy('price', 'asc')
        ->orderByDesc('rating')
        ->latest('published_at')
        ->oldest('created_at');

        $built = $q->build();

        $this->assertArrayHasKey('sort', $built);
        $this->assertIsArray($built['sort']);
        $this->assertArrayHasKey('_geo_distance', $built['sort'][0]);

        // ensure eloquent aliases produced sort entries
        $this->assertTrue(
            isset($built['sort'][1]['price']) && $built['sort'][1]['price']['order'] === 'asc'
        );
        $this->assertTrue(
            isset($built['sort'][2]['rating']) && $built['sort'][2]['rating']['order'] === 'desc'
        );

        // latest() should produce desc order for published_at
        $this->assertTrue(
            isset($built['sort'][3]['published_at']) && $built['sort'][3]['published_at']['order'] === 'desc'
        );
    }

    public function testSizeAndLimitValidation()
    {
        $q = new ElasticQuery();
        $q->size(5);
        $this->assertEquals(5, $q->build()['size']);

        $q2 = new ElasticQuery();
        $q2->limit(10);
        $this->assertEquals(10, $q2->build()['size']);

        $this->expectException(InvalidArgumentException::class);
        (new ElasticQuery())->size(-1);
    }
}
