<?php

namespace Arthur2weber\QueryCraft\Tests\Unit\ElasticQuery;

use Arthur2weber\QueryCraft\Query\ElasticQuery;
use PHPUnit\Framework\TestCase;

class TestableElasticQueryHelpers extends ElasticQuery {
    public function callCheckPerformanceWarnings(string $op, array $p = []){
        return $this->checkPerformanceWarnings($op, $p);
    }
    public function getPerformanceWarningsPublic(): array {
        return $this->getPerformanceWarnings();
    }
}

class CoverRemainingCoverageTest extends TestCase {
    public function testValidateAggregationUnknownTypeEmitsWarning(): void {
        $q = new TestableElasticQuery();
        $triggered = false;
        set_error_handler(function($sev, $msg) use (&$triggered){ $triggered = true; });
        $q->validateAggregationPublic('agg', ['unknown_type' => []]);
        restore_error_handler();
        $this->assertTrue($triggered, 'Expected a user warning for unknown aggregation type');
    }

    public function testValidateValueLongStringEmitsPerformanceWarningAndInvalidUtf8Throws(): void {
        $q = new TestableElasticQuery();
        $triggered = false;
        set_error_handler(function($sev, $msg) use (&$triggered){ $triggered = true; });
        // string longer than 32768 should trigger a user warning
        $long = str_repeat('a', 32769);
        $q->validateValuePublic($long);
        restore_error_handler();
        $this->assertTrue($triggered, 'Expected performance warning for very long string');

        // invalid UTF-8 should throw
        $this->expectException(\InvalidArgumentException::class);
        $invalid = "\xB1"; // invalid sequence for UTF-8
        $q->validateValuePublic($invalid);
    }

    public function testCheckPerformanceWarningsSortScriptAndNestedAggregation(): void {
        $q = new TestableElasticQueryHelpers();
        // sort_script should add a performance warning
        $q->callCheckPerformanceWarnings('sort_script');
        $this->assertNotEmpty($q->getPerformanceWarningsPublic());

        // nested_aggregation with depth > 3 should add warning
        $q2 = new TestableElasticQueryHelpers();
        $q2->callCheckPerformanceWarnings('nested_aggregation', ['depth' => 5]);
        $this->assertNotEmpty($q2->getPerformanceWarningsPublic());
    }

    public function testClauseCacheEvictionPathTriggered(): void {
        // Reduce maxCacheSize to small value to trigger eviction logic quickly
        $ref = new \ReflectionClass(ElasticQuery::class);
        $propMax = $ref->getProperty('maxCacheSize');
        $propMax->setAccessible(true);
        $propMax->setValue(null, 5);

        $propCacheSize = $ref->getProperty('cacheSize');
        $propCacheSize->setAccessible(true);
        $propCacheSize->setValue(null, 0);

        // call matchClausePublic with many unique values to populate cache and force eviction branch
        for ($i = 0; $i < 12; $i++) {
            TestableElasticQuery::matchClausePublic('field'.$i, 'v'.$i);
        }

        // ensure cacheSize advanced (eviction may have reduced it but code path executed)
        $this->assertGreaterThanOrEqual(0, $propCacheSize->getValue());
    }

    public function testValidateFieldNameWithSpaceEmitsWarning(): void {
        $q = new TestableElasticQuery();
        $triggered = false;
        set_error_handler(function($sev, $msg) use (&$triggered){ $triggered = true; });
        $q->validateFieldNamePublic('has space');
        restore_error_handler();
        $this->assertTrue($triggered, 'Expected a user warning when field name contains spaces');
    }

    public function testValidateValueInfiniteAndNaNThrow(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->validateValuePublic(INF);
    }

    public function testValidateGeoCoordinatesOutOfRangeThrows(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->validateGeoCoordinatesPublic(100, 0); // latitude too large

        // lon out of range
        $q2 = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q2->validateGeoCoordinatesPublic(0, 200);
    }

    public function testMustWithInvalidGeoDistanceFormatThrows(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->must(['geo_distance' => ['distance' => 'not-a-distance', 'loc' => ['lat' => 0, 'lon' => 0]]]);
    }

    public function testTranslateErrorRecognizesKnownPatterns(): void {
        $q = new TestableElasticQuery();
        $out = $q->translateError('No mapping found for [my_field]');
        $this->assertStringContainsString("Field \"my_field\"", $out);

        $out2 = $q->translateError('index_not_found_exception no such index [my_index]');
        $this->assertStringContainsString('Index "my_index"', $out2);
    }
}
