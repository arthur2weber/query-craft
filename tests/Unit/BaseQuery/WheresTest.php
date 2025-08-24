<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\BaseQuery;

class BaseQueryWheresTest extends TestCase
{
    public function testWhereAndWhereInValidate()
    {
        $q = new class extends BaseQuery {
            public function get(): array { return [];}
            public function first(): mixed { return null;}
            public function count(): int { return 0;}
            public function paginate(int $perPage = 15, int $page = 1): array { return [];}
            public function toQuery(): mixed { return [];}
        };

        $q->where('age', '>', 30);
        $this->assertNotEmpty($this->getProtectedProperty($q, 'wheres'));

        $q->whereIn('tags', ['php', 'unit']);
        $this->assertNotEmpty($this->getProtectedProperty($q, 'wheres'));
    }

    public function testWhereBetweenValidation()
    {
        $q = new class extends BaseQuery {
            public function get(): array { return [];}
            public function first(): mixed { return null;}
            public function count(): int { return 0;}
            public function paginate(int $perPage = 15, int $page = 1): array { return [];}
            public function toQuery(): mixed { return [];}
        };

        $this->expectException(InvalidArgumentException::class);
        $q->whereBetween('age', [1]);
    }

    private function getProtectedProperty($object, $prop)
    {
        $r = new ReflectionClass($object);
        $p = $r->getProperty($prop);
        $p->setAccessible(true);
        return $p->getValue($object);
    }
}
