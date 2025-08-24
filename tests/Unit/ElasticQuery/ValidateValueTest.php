<?php

namespace Tests\Unit\ElasticQuery;

require_once __DIR__ . '/TestableElasticQuery.php';

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\ElasticQuery as ElasticQueryClass;

class ValidateValueTest extends TestCase
{
    public function testValidateValueRejectsResourceAndCallable()
    {
        $q = new TestableElasticQuery();

        $ref = new \ReflectionClass(ElasticQueryClass::class);
        $m = $ref->getMethod('validateValue');
        $m->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $m->invoke($q, fopen(__FILE__, 'r'));
    }

    public function testValidateValueWarnsOnLongString()
    {
        $q = new TestableElasticQuery();
        $ref = new \ReflectionClass(ElasticQueryClass::class);
        $m = $ref->getMethod('validateValue');
        $m->setAccessible(true);

        $long = str_repeat('a', 33000);

        $warnings = [];
        $prev = set_error_handler(function($errno, $errstr) use (&$warnings) {
            if ($errno === E_USER_WARNING) {
                $warnings[] = $errstr;
                return true;
            }
            return false;
        });

        $m->invoke($q, $long);

        restore_error_handler();
        if ($prev !== null) {
            set_error_handler($prev);
        }

        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('String value is very long', $warnings[0]);
    }
}
