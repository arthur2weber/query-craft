<?php

namespace Tests\Unit\ElasticQuery\Utility;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TestableElasticQuery.php';

class ElasticQueryAdditionalHelpersTest extends TestCase
{
    private function makeTestable()
    {
        return new TestableElasticQuery();
    }

    private function captureWarning(callable $fn): array
    {
        $warnings = [];
        $handler = function($errno, $errstr) use (&$warnings) {
            if ($errno === E_USER_WARNING) {
                $warnings[] = $errstr;
                return true;
            }
            return false;
        };
        set_error_handler($handler, E_USER_WARNING);
        try {
            $fn();
        } finally {
            restore_error_handler();
        }
        return $warnings;
    }

    public function testWhereUnsupportedOperatorThrows()
    {
        $q = $this->makeTestable();
        $this->expectException(\InvalidArgumentException::class);
        $q->where('field', '%%', 'value');
    }

    public function testWhereRangeOperatorsMapToRangeClauses()
    {
        $q = $this->makeTestable();
        $q->where('age', '>', 30);
        $q->where('age', '>=', 18);
        $q->where('age', '<', 65);
        $q->where('age', '<=', 99);

        $built = $q->build();
        $this->assertArrayHasKey('query', $built);
        // ensure filters or must contain range structures
        $foundRange = false;
        foreach ($built['query']['bool']['filter'] as $f) {
            if (isset($f['range'])) {
                $foundRange = true;
                break;
            }
        }
        $this->assertTrue($foundRange, 'Expected at least one range clause in filters');
    }

    public function testWhereInAndWhereNotInRequireNonEmptyAndValidateValues()
    {
        $q = $this->makeTestable();
        $this->expectException(\InvalidArgumentException::class);
        $q->whereIn('tags', []);

        $this->expectException(\InvalidArgumentException::class);
        $q->whereNotIn('tags', []);

        // valid usage
        $q2 = $this->makeTestable();
        $q2->whereIn('tags', ['a', 'b']);
        $q2->whereNotIn('status', ['x']);
        $this->assertArrayHasKey('query', $q2->build());
    }

    public function testWhereBetweenValidationAndOrdering()
    {
        $q = $this->makeTestable();
        $this->expectException(\InvalidArgumentException::class);
        $q->whereBetween('num', [1]);

        $this->expectException(\InvalidArgumentException::class);
        $q->whereBetween('num', [5, 1]); // min > max

        // valid
        $q2 = $this->makeTestable();
        $q2->whereBetween('num', [1, 5]);
        $built = $q2->build();
        $this->assertArrayHasKey('query', $built);
    }

    public function testWhereNullAndWhereNotNullBuildExistenceClauses()
    {
        $q = $this->makeTestable();
        $q->whereNull('deleted_at');
        $q->whereNotNull('published_at');
        $built = $q->build();
        $this->assertArrayHasKey('query', $built);
        $this->assertNotEmpty($built['query']['bool']['must']);
    }

    public function testOrderByHelpersDelegateToSort()
    {
        $q = $this->makeTestable();
        $q->orderBy('col', 'asc');
        $q->orderByDesc('col2');
        $q->latest('created_at');
        $q->oldest('created_at');
        $this->assertArrayHasKey('sort', $q->build());
    }

    public function testPaginateValidatesArgumentsAndReturnsStructure()
    {
        $q = $this->makeTestable();
        $this->expectException(\InvalidArgumentException::class);
        $q->paginate(10, 0);

        $this->expectException(\InvalidArgumentException::class);
        $q->paginate(0, 1);

        $q2 = $this->makeTestable();
        $res = $q2->paginate(5, 2);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('data', $res);
        $this->assertArrayHasKey('per_page', $res);
    }

    public function testSearchEmptyStringProducesWarning()
    {
        $q = $this->makeTestable();
        $warnings = $this->captureWarning(function() use ($q) {
            $q->search('', ['title']);
        });
        // ElasticQuery->search uses addWarning (not trigger), so expect no user warning but warnings via getWarnings
        $this->assertNotEmpty($q->getWarnings());
    }

    public function testSourceArrayValidation()
    {
        $q = $this->makeTestable();
        $this->expectException(\InvalidArgumentException::class);
        $q->source([]);

        $q2 = $this->makeTestable();
        $q2->source(['a', 'b']);
        $this->assertArrayHasKey('_source', $q2->build());
    }

    public function testWhenUnlessBehavior()
    {
        $q = $this->makeTestable();
        $res = $q->when(true, function($r) { return $r->orderBy('x'); });
        $this->assertSame($q, $res);

        $res2 = $q->unless(true, function($r) { return $r->orderBy('y'); });
        $this->assertSame($q, $res2);
    }
}

// Split into focused tests under Filters/ and Aggregation/.
// This file retained for backward compatibility but heavy tests moved to targeted files.
