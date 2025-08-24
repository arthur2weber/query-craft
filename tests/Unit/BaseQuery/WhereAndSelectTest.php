<?php

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\BaseQuery;

class BaseQueryStub extends BaseQuery
{
    public function get(): array { return [];} 
    public function first(): mixed { return null;}
    public function count(): int { return 0;}
    public function paginate(int $perPage = 15, int $page = 1): array { return [];}
    public function toQuery(): mixed { return [];}
}

class WhereAndSelectTest extends TestCase
{
    public function testFromSetsCollectionAndOffset()
    {
        $q = new BaseQueryStub();
        $q->from('users');
        $this->assertSame('users', $this->getProtectedProperty($q, 'from'));

        $q->from(10);
        $this->assertSame(10, $this->getProtectedProperty($q, 'offsetValue'));
    }

    public function testSelectAndVerboseAndWarnings()
    {
        $q = new BaseQueryStub();
        $q->select(['id', 'name']);
        $this->assertSame(['id', 'name'], $this->getProtectedProperty($q, 'selects'));

        $q->verbose(true);
        $this->assertTrue($this->getProtectedProperty($q, 'verboseMode'));

        $this->expectException(InvalidArgumentException::class);
        $q->where('', 1);
    }

    private function getProtectedProperty($object, $prop)
    {
        $r = new ReflectionClass($object);
        $p = $r->getProperty($prop);
        $p->setAccessible(true);
        return $p->getValue($object);
    }
}
