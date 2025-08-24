<?php

namespace Tests\Unit\ElasticQuery\Utility;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TestableElasticQuery.php';

class ElasticQueryHelpersTest extends TestCase
{
    public function testPlaceholder(): void
    {
        // Minimal assertion so PHPUnit treats this as a valid test class.
        $this->addToAssertionCount(1);
    }
}
