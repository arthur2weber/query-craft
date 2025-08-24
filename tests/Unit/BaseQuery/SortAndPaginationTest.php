<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\BaseQuery;

class SortAndPaginationTest extends TestCase
{
    public function testOrderByAndLimitSkip()
    {
        $q = new class extends BaseQuery {
            public function get(): array { return [];}
            public function first(): mixed { return null;}
            public function count(): int { return 0;}
            public function paginate(int $perPage = 15, int $page = 1): array { return [];}
            public function toQuery(): mixed { return [];}
        };

        $q->orderBy('name', 'desc');
        $this->assertNotEmpty($this->getProtectedProperty($q, 'sorts'));

        $q->take(5);
        $this->assertSame(5, $this->getProtectedProperty($q, 'limitValue'));

        $q->skip(2);
        $this->assertSame(2, $this->getProtectedProperty($q, 'offsetValue'));
    }

    private function getProtectedProperty($object, $prop)
    {
        $r = new ReflectionClass($object);
        $p = $r->getProperty($prop);
        $p->setAccessible(true);
        return $p->getValue($object);
    }
}
