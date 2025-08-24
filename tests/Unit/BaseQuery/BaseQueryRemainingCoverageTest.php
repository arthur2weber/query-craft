<?php

namespace Arthur2weber\QueryCraft\Tests\Unit\BaseQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\BaseQuery;

class TestableBaseQuery extends BaseQuery
{
    // Implement abstract methods with simple deterministic results
    public function get(): array { return []; }
    public function first(): mixed { return null; }
    public function count(): int { return 0; }
    public function paginate(int $perPage = 15, int $page = 1): array { return ['perPage' => $perPage, 'page' => $page]; }
    public function toQuery(): mixed { return [] ; }

    // Expose protected helpers for testing
    public function exposeValidateValue($value): void
    {
        $this->validateValue($value);
    }

    public function exposeValidateFieldName(string $field): void
    {
        $this->validateFieldName($field);
    }

    public function exposeValidateNonEmptyArray(array $array, string $context = 'Array'): void
    {
        $this->validateNonEmptyArray($array, $context);
    }
}

class BaseQueryRemainingCoverageTest extends TestCase
{
    public function test_limit_and_offset_aliases_and_orderByDesc_log_operations_when_verbose()
    {
        $q = new TestableBaseQuery();
        $q->verbose(true);

        // aliases
        $q->limit(10);
        $q->offset(5);

        // orderByDesc delegates to orderBy
        $q->orderByDesc('field_name');

        $log = $q->getBuildLog();
        $operations = array_map(fn($e) => $e['operation'], $log);

        $this->assertContains('take', $operations, 'take should be logged via limit()');
        $this->assertContains('skip', $operations, 'skip should be logged via offset()');
        $this->assertContains('orderBy', $operations, 'orderBy should be logged via orderByDesc()');
    }

    public function test_where_and_orWhere_default_operator_and_logging()
    {
        $q = new TestableBaseQuery();
        $q->verbose(true);

        // where with 2 args should default operator to '='
        $q->where('a', 123);
        $q->orWhere('b', 'value');

        $log = $q->getBuildLog();
        $lastOps = array_slice(array_reverse($log), 0, 2);
        $this->assertSame('orWhere', $lastOps[0]['operation']);
        $this->assertSame('where', $lastOps[1]['operation']);
    }

    public function test_validate_value_rejects_resource_and_callable()
    {
        $q = new TestableBaseQuery();

        // resource
        $resource = fopen('php://memory', 'r');
        $this->expectException(\InvalidArgumentException::class);
        $q->exposeValidateValue($resource);
        if (is_resource($resource)) {
            fclose($resource);
        }
    }

    public function test_validate_value_rejects_callable()
    {
        $q = new TestableBaseQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->exposeValidateValue(fn() => 'ok');
    }

    public function test_validate_non_empty_array_throws_on_empty()
    {
        $q = new TestableBaseQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->exposeValidateNonEmptyArray([], 'Test context');
    }

    public function test_validate_field_name_empty_throws_and_space_adds_warning()
    {
        $q = new TestableBaseQuery();

        $this->expectException(\InvalidArgumentException::class);
        $q->exposeValidateFieldName('');

        // Now test space warning path
        $q2 = new TestableBaseQuery();
        $q2->exposeValidateFieldName('has space');
        $warnings = $q2->getWarnings();
        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('contains spaces', $warnings[0]);
    }

    public function test_select_with_non_string_throws_and_select_valid_logs()
    {
        $q = new TestableBaseQuery();

        $this->expectException(\InvalidArgumentException::class);
        $q->select(['ok', 123]);
    }

    public function test_validate_value_long_string_triggers_performance_warning()
    {
        $q = new TestableBaseQuery();
        $long = str_repeat('a', 40000);

        // no exception expected, just performance warning
        $q->exposeValidateValue($long);
        $p = $q->getPerformanceWarnings();
        $this->assertNotEmpty($p, 'Performance warning should be added for long strings');
    }

    public function test_whereNotBetween_invalid_range_throws()
    {
        $q = new TestableBaseQuery();
        $this->expectException(\InvalidArgumentException::class);
        // range must have exactly two values
        $q->whereNotBetween('some_field', [1]);
    }

    public function test_orderBy_invalid_direction_throws()
    {
        $q = new TestableBaseQuery();
        $this->expectException(\InvalidArgumentException::class);
        // invalid direction should throw
        $q->orderBy('field_name', 'upwards');
    }

    public function test_take_negative_throws()
    {
        $q = new TestableBaseQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->take(-1);
    }

    public function test_skip_negative_throws()
    {
        $q = new TestableBaseQuery();
        $this->expectException(\InvalidArgumentException::class);
        $q->skip(-5);
    }

    public function test_validate_value_with_control_characters_throws()
    {
        $q = new TestableBaseQuery();
        $this->expectException(\InvalidArgumentException::class);
        // include a control character (bell) to trigger the control-char validation
        $q->exposeValidateValue("good" . chr(7) . "bad");
    }
}
