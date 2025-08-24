<?php

namespace Tests\Unit\MongoQuery;

use PHPUnit\Framework\TestCase;
use Arthur2weber\QueryCraft\Query\MongoQuery;

class BoostWrappersTest extends TestCase
{
    public function testTermAndMatchBoostWrappers()
    {
        $tb = MongoQuery::termBoost('i', 'v', 2.5);
        $this->assertSame(['i' => 'v'], $tb);
        $mb = MongoQuery::matchBoost(['j' => 1], 3.0);
        $this->assertSame(['$match' => ['j' => 1]], $mb);
    }
}
