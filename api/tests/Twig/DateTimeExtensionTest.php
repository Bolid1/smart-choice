<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Twig\DateTimeExtension;
use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class DateTimeExtensionTest extends TestCase
{
    /**
     * @dataProvider intervalsProvider
     *
     * @covers \App\Twig\DateTimeExtension::__construct
     * @covers \App\Twig\DateTimeExtension::dateAgo
     *
     * @param string $msg
     * @param int $count
     * @param DateInterval $interval
     */
    public function testDateAgo(string $msg, int $count, DateInterval $interval): void
    {
        $extension = new DateTimeExtension($translator = $this->createMock(TranslatorInterface::class));
        $now = new DateTimeImmutable();

        $translator
            ->expects($this->once())
            ->method('trans')
            ->with($msg, ['%count%' => $count])
            ->willReturn($expected = 'some interval')
        ;

        $this->assertEquals($expected, $extension->dateAgo($now->sub($interval)));
    }

    public function intervalsProvider()
    {
        return [
            ['day|%count% days', 5, new DateInterval('P5D')],
            ['hour|%count% hours', 3, new DateInterval('PT3H')],
            ['minute|%count% minutes', 1, new DateInterval('PT1M')],
            ['second|%count% seconds', 9, new DateInterval('PT9S')],
        ];
    }
}
