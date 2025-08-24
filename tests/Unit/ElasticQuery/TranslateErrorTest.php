<?php

namespace Tests\Unit\ElasticQuery;

require_once __DIR__ . '/TestableElasticQuery.php';

use PHPUnit\Framework\TestCase;

class TranslateErrorTest extends TestCase
{
    public function testTranslateKnownPatterns()
    {
        $q = new TestableElasticQuery();

        $msg = $q->translateError('No mapping found for [title]');
        $this->assertStringContainsString("Field \"title\" doesn't exist", $msg);

        $msg2 = $q->translateError('index_not_found_exception: no such index [books]');
        $this->assertStringContainsString('Index "books" doesn\'t exist', $msg2);

        $msg3 = $q->translateError('some random error');
        $this->assertStringContainsString('We encountered an Elasticsearch error', $msg3);
    }
}
