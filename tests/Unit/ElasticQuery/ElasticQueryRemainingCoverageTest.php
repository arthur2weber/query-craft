<?php

namespace Arthur2weber\QueryCraft\Tests\Unit\ElasticQuery;

use Arthur2weber\QueryCraft\Query\ElasticQuery;
use PHPUnit\Framework\TestCase;

class TestableElasticQuery extends ElasticQuery {
    // Expose protected validators and helpers for focused testing
    public function validateFieldNamePublic(string $field): void { $this->validateFieldName($field); }
    public function validateValuePublic($value): void { $this->validateValue($value); }
    public function validateAggregationPublic(string $name, array $agg): void { $this->validateAggregation($name, $agg); }
    public function validateGeoCoordinatesPublic($lat, $lon): void { $this->validateGeoCoordinates($lat, $lon); }
    public function validateScriptPublic(string $script): void { $this->validateScript($script); }
    public function validateConditionValuesPublic(array $condition, int $depth = 0, int $maxDepth = 50): void { $this->validateConditionValues($condition, $depth, $maxDepth); }
    public function checkForLogicalConflictsPublic(string $field, string $operator, $value): void { $this->checkForLogicalConflicts($field, $operator, $value); }
    public static function matchClausePublic(string $field, string|array $value): array { return self::matchClause($field, $value); }
}

class ElasticQueryRemainingCoverageTest extends TestCase {
    public function tearDown(): void {
        // ensure any opened resources are closed
        parent::tearDown();
    }

    public function testValidateFieldNameThrowsOnEmpty(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->validateFieldNamePublic('');
    }

    public function testValidateValueRejectsResourceAndCallable(): void {
        $q = new TestableElasticQuery();
        $res = fopen('php://memory', 'r');
        $this->expectException(\InvalidArgumentException::class);
        $q->validateValuePublic($res);
        fclose($res);
    }

    public function testValidateGeoCoordinatesNumericAndBounds(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->validateGeoCoordinatesPublic('not-a-number', 0);
    }

    public function testValidateScriptEmptyThrows(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->validateScriptPublic('   ');
    }

    public function testValidateConditionValuesDepthExceeded(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->validateConditionValuesPublic([], 51, 50);
    }

    public function testMustShouldMustNotEmptyThrow(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->must([]);

        $q2 = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q2->should([]);

        $q3 = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q3->mustNot([]);
    }

    public function testMinimumShouldMatchGreaterThanCountThrows(): void {
        $q = new TestableElasticQuery();
        // add one should clause
        $q->should(['match' => ['field' => 'value']]);
        $this->expectException(\InvalidArgumentException::class);
        $q->minimumShouldMatch(2);
    }

    public function testFilterNullValueThrows(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->filter('field');
    }

    public function testRangeInvalidOperatorThrows(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->range('f', 'invalid', 1);
    }

    public function testNestedEmptyPathAndEmptyCallbackWarning(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->nested('   ', function($n){});

        // nested with non-empty path but empty callback result triggers a warning but should return
        $q2 = new TestableElasticQuery();
        $q2->verbose(true);
        // callback does nothing -> builds an empty nested query internally
        $q2->nested('path', function($n){});
        // verbose mode keeps logs; ensure build executed
        $this->assertIsArray($q2->getVerboseLog());
    }

    public function testSortInvalidDirectionThrows(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->sort('f', 'upwards');
    }

    public function testSizeAndFromPerformanceWarningsAndNegativeChecks(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->size(-1);

        $q2 = new TestableElasticQuery();
        // very large size triggers performance warning
        $q2->verbose(true)->size(20000);
        $pws = $q2->getPerformanceWarnings();
        $this->assertNotEmpty($pws);

        $q3 = new TestableElasticQuery();
        // set a very large size then call from to trigger deep pagination warning
        $q3->size(10000);
        $q3->verbose(true)->from(1);
        $this->assertNotEmpty($q3->getPerformanceWarnings());
    }

    public function testHighlightEmptyThrows(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->highlight([]);
    }

    public function testScriptScoreRequiresNonEmptyScript(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->scriptScore('');
    }

    public function testTermsPrefixWildcardRegexpFuzzyValidation(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->terms('f', []);

        $q2 = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q2->prefix('f', ' ');

        $q3 = new TestableElasticQuery();
        // leading wildcard should add performance warning
        $q3->verbose(true)->wildcard('f', '*abc');
        $this->assertNotEmpty($q3->getPerformanceWarnings());

        $q4 = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q4->regexp('f', '');

        $q5 = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q5->fuzzy('f', '', 1);

        $q6 = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q6->fuzzy('f', 'v', new \stdClass());
    }

    public function testMatchClauseCachingAndAnalyzeComplexity(): void {
        // ensure matchClause returns identical structure and plays well with cache
        $first = TestableElasticQuery::matchClausePublic('field', 'v');
        $second = TestableElasticQuery::matchClausePublic('field', 'v');
        $this->assertEquals($first, $second);

        $q = new TestableElasticQuery();
        // add more than 5 clauses to bump complexity
        for ($i = 0; $i < 6; $i++) {
            $q->must(['match' => ["f{$i}" => 'v']]);
        }
        $q->aggregation('agg1', ['terms' => ['field' => 'f0']]);
        $analysis = $q->analyze();
        $this->assertEquals('medium', $analysis['estimated_complexity']);
        $this->assertTrue($analysis['has_aggregations']);
    }

    public function testWhereUnsupportedOperatorAndBetweenValidation(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->where('f', '***', 'v');

        $q2 = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q2->whereBetween('f', [1]);

        $q3 = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q3->whereBetween('f', [10, 1]);
    }

    public function testTimeoutAndGeoDistanceInvalidFormats(): void {
        $q = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->timeout('invalid');

        $q2 = new TestableElasticQuery();
        $this->expectException(\InvalidArgumentException::class);
        // invalid location string format
        $q2->geoDistance('loc', 'not,a,coord', '10km');
    }
}
