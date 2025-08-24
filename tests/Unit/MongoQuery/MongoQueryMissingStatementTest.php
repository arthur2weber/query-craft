<?php

namespace Tests\Unit\MongoQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class MongoQueryMissingStatementTest extends TestCase
{
    public function testTopLevelOperatorFallbackAssignment(): void
    {
        $q = new MongoQuery();

        // Prepare two where-clauses that will produce a top-level operator key
        // (field starting with $). The first sets a scalar value, the second an array.
        // This exercises the branch that falls back to assignment when merge is not possible
        // (line that previously showed as not covered in coverage.xml).
        $wheres = [
            [
                'field' => '$or',
                'operator' => '=',
                'value' => 'scalar',
                'type' => 'where',
                'boolean' => 'and'
            ],
            [
                'field' => '$or',
                'operator' => '=',
                'value' => ['array-value'],
                'type' => 'where',
                'boolean' => 'and'
            ]
        ];

        $rp = new \ReflectionProperty(MongoQuery::class, 'wheres');
        $rp->setAccessible(true);
        // Use the two-argument setValue signature to avoid PHP deprecation warnings
        $rp->setValue($q, $wheres);

        // Build the query (will call buildMongoFilter internally)
        $built = $q->build();

        // Expect the second where to have overwritten the first for the top-level $or key
        $this->assertArrayHasKey('filter', $built);
        $this->assertArrayHasKey('$or', $built['filter']);
        $this->assertSame(['array-value'], $built['filter']['$or']);
    }
}
