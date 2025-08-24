<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\BaseQuery;

class TestableBaseQuery extends BaseQuery
{
    // Expose protected methods for testing
    public function validateFieldNamePublic(string $field): void
    {
        $this->validateFieldName($field);
    }

    public function validateValuePublic($value): void
    {
        $this->validateValue($value);
    }

    public function validateNonEmptyArrayPublic(array $arr, string $context = 'Array'): void
    {
        $this->validateNonEmptyArray($arr, $context);
    }

    public function logOperationPublic(string $op, array $params = []): void
    {
        $this->logOperation($op, $params);
    }

    // Implement abstract methods with simple stubs
    public function get(): array { return []; }
    public function first(): mixed { return null; }
    public function count(): int { return 0; }
    public function paginate(int $perPage = 15, int $page = 1): array { return []; }
    public function toQuery(): mixed { return null; }
}

class BaseQueryCoverageTest extends TestCase
{
    public function testValidateFieldNameEmptyThrows()
    {
        $q = new TestableBaseQuery();
        $this->expectException(InvalidArgumentException::class);
        $q->validateFieldNamePublic('');
    }

    public function testValidateFieldNameWithSpaceAddsWarning()
    {
        $q = new TestableBaseQuery();
        $q->validateFieldNamePublic('has space');
        $warnings = $q->getWarnings();
        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('contains spaces', $warnings[0]);
    }

    public function testValidateValueRejectsResourceAndCallable()
    {
        $q = new TestableBaseQuery();

        $res = fopen('php://memory', 'r');
        try {
            $this->expectException(InvalidArgumentException::class);
            $q->validateValuePublic($res);
        } finally {
            if (is_resource($res)) { fclose($res); }
        }

        $this->expectException(InvalidArgumentException::class);
        $q->validateValuePublic(function () {});
    }

    public function testValidateValueRejectsNonFinite()
    {
        $q = new TestableBaseQuery();
        $this->expectException(InvalidArgumentException::class);
        $q->validateValuePublic(NAN);
    }

    public function testValidateValueRejectsNonUtf8AndControlChars()
    {
        $q = new TestableBaseQuery();

        $this->expectException(InvalidArgumentException::class);
        $invalid = pack('C*', 0x80);
        $q->validateValuePublic($invalid);

        // control chars
        $this->expectException(InvalidArgumentException::class);
        $q->validateValuePublic("hello\x01world");
    }

    public function testValidateValueLongStringTriggersPerformanceWarning()
    {
        $q = new TestableBaseQuery();
        $long = str_repeat('a', 33000);
        $q->validateValuePublic($long);

        $p = $q->getPerformanceWarnings();
        $this->assertNotEmpty($p);
        $this->assertStringContainsString('very long', $p[0]);
    }

    public function testValidateNonEmptyArrayThrows()
    {
        $q = new TestableBaseQuery();
        $this->expectException(InvalidArgumentException::class);
        $q->validateNonEmptyArrayPublic([], 'TestContext');
    }

    public function testLogOperationWhenVerboseAddsToBuildLog()
    {
        $q = new TestableBaseQuery();
        $q->verbose(true);
        $q->logOperationPublic('testOp', ['x' => 1]);
        $log = $q->getBuildLog();
        $this->assertNotEmpty($log);
        $this->assertEquals('testOp', $log[0]['operation']);
    }
}
