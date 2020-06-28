<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Helper\DateTimeHelper;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class DateTimeHelperTest extends TestCase
{
    /**
     * @covers \App\Helper\DateTimeHelper::toImmutable
     */
    public function testToImmutable(): void
    {
        $date = new DateTime('-1 day 3 hours 35 seconds');
        $this->assertEquals($date->getTimestamp(), DateTimeHelper::toImmutable($date)->getTimestamp());
        $date = new DateTimeImmutable('-6 days 4 hours 12 minutes');
        $this->assertSame($date, DateTimeHelper::toImmutable($date));
    }
}
