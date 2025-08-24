<?php

namespace Tests\Unit\BaseQuery;

use PHPUnit\Framework\TestCase;

class BaseQueryHelpersTest extends TestCase
{
    /**
     * Test-only subclass that exposes protected BaseQuery helpers
     */
    public function testValidateHelpersAndWarnings()
    {
        // Define a small test-only subclass inside the test to access protected methods
        $self = $this;

        eval(<<<'PHP'
        namespace Tests\Unit\BaseQuery;

        class TestableBaseQuery extends \Arthur2weber\QueryCraft\Query\BaseQuery
        {
            public function callValidateFieldName(string $field)
            {
                return $this->validateFieldName($field);
            }

            public function callValidateValue($value)
            {
                return $this->validateValue($value);
            }

            public function callValidateNonEmptyArray(array $array, string $context = 'Array')
            {
                return $this->validateNonEmptyArray($array, $context);
            }

            public function callLogOperation(string $op, array $params = [])
            {
                return $this->logOperation($op, $params);
            }

            public function callAddWarning(string $msg)
            {
                return $this->addWarning($msg);
            }

            public function callAddPerformanceWarning(string $msg)
            {
                return $this->addPerformanceWarning($msg);
            }

            // Implement abstract methods from BaseQuery
            public function get(): array { return []; }
            public function first(): mixed { return null; }
            public function count(): int { return 0; }
            public function paginate(int $perPage = 15, int $page = 1): array { return []; }
            public function toQuery(): mixed { return ''; }
        }
        PHP
        );

        $q = new TestableBaseQuery();

        // validateNonEmptyArray: empty should throw
        $this->expectException(\InvalidArgumentException::class);
        $q->callValidateNonEmptyArray([], 'TestContext');
    }

    public function testValidateFieldNameAndLogOperationAndWarnings()
    {
        eval(<<<'PHP'
        namespace Tests\Unit\BaseQuery;
        if (!class_exists('Tests\\Unit\\BaseQuery\\TestableBaseQuery')) {
            class TestableBaseQuery extends \Arthur2weber\QueryCraft\Query\BaseQuery
            {
                public function callValidateFieldName(string $field)
                {
                    return $this->validateFieldName($field);
                }

                public function callValidateValue($value)
                {
                    return $this->validateValue($value);
                }

                public function callValidateNonEmptyArray(array $array, string $context = 'Array')
                {
                    return $this->validateNonEmptyArray($array, $context);
                }

                public function callLogOperation(string $op, array $params = [])
                {
                    return $this->logOperation($op, $params);
                }

                public function callAddWarning(string $msg)
                {
                    return $this->addWarning($msg);
                }

                public function callAddPerformanceWarning(string $msg)
                {
                    return $this->addPerformanceWarning($msg);
                }

                // Implement abstract methods from BaseQuery
                public function get(): array { return []; }
                public function first(): mixed { return null; }
                public function count(): int { return 0; }
                public function paginate(int $perPage = 15, int $page = 1): array { return []; }
                public function toQuery(): mixed { return ''; }
            }
        }
        PHP
        );

        $q = new TestableBaseQuery();

        // Empty and whitespace-only field names should throw
        $this->expectException(\InvalidArgumentException::class);
        $q->callValidateFieldName('');

        // whitespace-only
        try {
            $q->callValidateFieldName("   ");
            $this->fail('Expected InvalidArgumentException for whitespace-only field');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('Field name cannot be empty', $e->getMessage());
        }

        // Field with space should NOT throw but should add a warning
        $q->callValidateFieldName('my field');
        $warnings = $q->getWarnings();
        $this->assertNotEmpty($warnings, 'Expected warnings to contain message about space in field name');

        // Test logOperation only logs when verbose
        $q->verbose(true);
        $q->callLogOperation('from', ['collection' => 'test']);
        $log = $q->getBuildLog();
        $this->assertNotEmpty($log, 'Expected build log to contain an entry when verbose is enabled');
        $found = false;
        foreach ($log as $entry) {
            if (is_array($entry) && ($entry['operation'] ?? '') === 'from') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected a log entry for operation "from"');

        // Test addWarning and addPerformanceWarning public wrappers
        $q->callAddWarning('test-warning');
        $q->callAddPerformanceWarning('perf-warning');
        $allWarnings = $q->getWarnings();
        $perfWarnings = $q->getPerformanceWarnings();
        $this->assertContains('test-warning', $allWarnings);
        $this->assertContains('perf-warning', $perfWarnings);
    }

    public function testValidateValueRejectsUnsupportedTypesAndDetectsLongString()
    {
        eval(<<<'PHP'
        namespace Tests\Unit\BaseQuery;
        if (!class_exists('Tests\\Unit\\BaseQuery\\TestableBaseQuery')) {
            class TestableBaseQuery extends \Arthur2weber\QueryCraft\Query\BaseQuery
            {
                public function callValidateFieldName(string $field)
                {
                    return $this->validateFieldName($field);
                }

                public function callValidateValue($value)
                {
                    return $this->validateValue($value);
                }

                public function callValidateNonEmptyArray(array $array, string $context = 'Array')
                {
                    return $this->validateNonEmptyArray($array, $context);
                }

                public function callLogOperation(string $op, array $params = [])
                {
                    return $this->logOperation($op, $params);
                }

                public function callAddWarning(string $msg)
                {
                    return $this->addWarning($msg);
                }

                public function callAddPerformanceWarning(string $msg)
                {
                    return $this->addPerformanceWarning($msg);
                }

                // Implement abstract methods from BaseQuery
                public function get(): array { return []; }
                public function first(): mixed { return null; }
                public function count(): int { return 0; }
                public function paginate(int $perPage = 15, int $page = 1): array { return []; }
                public function toQuery(): mixed { return ''; }
            }
        }
        PHP
        );

        $q = new TestableBaseQuery();

        // Resource should be rejected
        $res = fopen('php://memory', 'r');
        $this->expectException(\InvalidArgumentException::class);
        $q->callValidateValue($res);
        if (is_resource($res)) {
            fclose($res);
        }

        // Callable should be rejected
        $this->expectException(\InvalidArgumentException::class);
        $q->callValidateValue(function() {});

        // Non-finite numeric should be rejected
        $this->expectException(\InvalidArgumentException::class);
        $q->callValidateValue(INF);

        // Control characters in string should be rejected
        $this->expectException(\InvalidArgumentException::class);
        $q->callValidateValue("abc\x01def");

        // Very long string should add a performance warning rather than throw
        $long = str_repeat('a', 33000);
        // call without expecting exception
        $q->callValidateValue($long);
        $perf = $q->getPerformanceWarnings();
        $this->assertNotEmpty($perf, 'Expected performance warnings for very long strings');
    }
}
