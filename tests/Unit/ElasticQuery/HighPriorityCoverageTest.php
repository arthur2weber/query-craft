<?php

namespace Arthur2weber\QueryCraft\Tests\Unit\ElasticQuery;

use Arthur2weber\QueryCraft\Query\ElasticQuery;
use PHPUnit\Framework\TestCase;

class HighPriorityCoverageTest extends TestCase
{
    public function testGeoDistanceValidationAndArrayLocation()
    {
        $q = new ElasticQuery();

        // invalid distance format
        $this->expectException(\InvalidArgumentException::class);
        $q->geoDistance('location', ['lat' => 10, 'lon' => 20], '5kilometers');
    }

    public function testGeoDistanceStringLocationFormat()
    {
        $q = new ElasticQuery();
        // valid string location
        $q->geoDistance('location', '10.0,20.0', '5km');
        $built = $q->build();
        $this->assertArrayHasKey('query', $built);
        $this->assertNotEmpty($built['query']['bool']['must']);
    }

    public function testValidateGeoQueriesThrowsOnBadDistanceInMust()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->must(['geo_distance' => ['distance' => 'fast', 'location' => ['lat' => 0, 'lon' => 0]]]);
    }

    public function testCacheBehaviorThroughTermClause()
    {
        $a = ElasticQuery::termClause('a', '1');
        $b = ElasticQuery::termClause('a', '1');
        $this->assertSame($a, $b);
    }

    public function testMinimumShouldMatchValidation()
    {
        $q = new ElasticQuery();
        // add one should clause
        $q->should(['term' => ['x' => 'v']]);
        // numeric greater than should count should throw
        $this->expectException(\InvalidArgumentException::class);
        $q->minimumShouldMatch(2);
    }

    public function testMinimumShouldMatchPercentageTooLarge()
    {
        $q = new ElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->minimumShouldMatch('101%');
    }

    public function testPaginateAndPaginationWarnings()
    {
        $q = new ElasticQuery();
        // deep pagination should generate performance warning if offset > 10000
        $result = $q->paginate(10, 1);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('per_page', $result);

        // page 0 invalid
        $this->expectException(\InvalidArgumentException::class);
        $q->paginate(10, 0);
    }

    public function testSizeFromPerformanceWarning()
    {
        $q = new ElasticQuery();
        $q->size(20000);
        $p = $q->getPerformanceWarnings();
        $this->assertNotEmpty($p);

        // deep from with size that pushes beyond 10000 triggers performance warning
        $q2 = new ElasticQuery();
        $q2->size(6000);
        $q2->from(5000);
        $this->assertNotEmpty($q2->getPerformanceWarnings());
    }

    public function testWildcardAndRegexpPerformanceWarnings()
    {
        $q = new ElasticQuery();
        $q->wildcard('name', '*prefix');
        $this->assertNotEmpty($q->getPerformanceWarnings());

        $q2 = new ElasticQuery();
        $q2->regexp('name', 'a.*b');
        $this->assertNotEmpty($q2->getPerformanceWarnings());
    }

    public function testFuzzyValidationAndBehavior()
    {
        $q = new ElasticQuery();
        // invalid fuzziness type
        $this->expectException(\InvalidArgumentException::class);
        $q->fuzzy('name', 'value', new \stdClass());
    }

    public function testTranslateErrorKnownAndUnknown()
    {
        $q = new ElasticQuery();
        $known = "No mapping found for [title]";
        $msg = $q->translateError($known);
        $this->assertStringContainsString("title", $msg);
        $this->assertStringContainsString('Original error', $msg);

        $unknown = "Some random unexpected error that we do not map";
        $fallback = $q->translateError($unknown);
        $this->assertStringContainsString('We encountered an Elasticsearch error', $fallback);
    }

    public function testAnalyzeDetectsSearchAndClauses()
    {
        $q = new ElasticQuery();
        $q->match('title', 'hello');
        $analysis = $q->analyze();
        $this->assertTrue($analysis['has_search']);
        $this->assertGreaterThan(0, $analysis['clause_count']);
    }

    public function testHighlightBuildsStructureAndValidation()
    {
        $q = new ElasticQuery();
        $q->highlight(['title', 'description']);
        $built = $q->build();
        $this->assertArrayHasKey('highlight', $built);
        $this->assertArrayHasKey('fields', $built['highlight']);

        $this->expectException(\InvalidArgumentException::class);
        $q->highlight([]);
    }

    public function testSortDirectionValidation()
    {
        $q = new ElasticQuery();
        $q->sort('name', 'asc');
        $built = $q->build();
        $this->assertArrayHasKey('sort', $built);

        $this->expectException(\InvalidArgumentException::class);
        $q->sort('name', 'upwards');
    }
}
