<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Twig\ElapsedTimeExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;

class ElapsedTimeExtensionTest extends TestCase
{
    public function testGetFiltersReturnsElapsedTimeFilter(): void
    {
        $extension = new ElapsedTimeExtension();

        $filters = $extension->getFilters();

        self::assertCount(1, $filters);
        self::assertInstanceOf(TwigFilter::class, $filters[0]);
        self::assertSame('elapseTime', $filters[0]->getName());
        self::assertSame([$extension, 'formatElapsedTime'], $filters[0]->getCallable());
    }

    /**
     * @dataProvider provideElapsedTimeValues
     */
    public function testFormatElapsedTimeWithScalarValues(int|string|\DateInterval $seconds, string $expected): void
    {
        $extension = new ElapsedTimeExtension();

        self::assertSame($expected, $extension->formatElapsedTime($seconds));
    }

    public function provideElapsedTimeValues(): iterable
    {
        yield 'zero seconds' => [0, '0:00'];
        yield 'less than one minute rounds up to one minute' => [59, '0:01'];
        yield 'exactly one hour' => [3600, '1:00'];
        yield 'one hour one second rounds minutes up' => [3601, '1:01'];
        yield 'numeric string input' => ['7200', '2:00'];
        yield 'DateInterval diff input' => [(new \DateTime('2026-01-01 01:00:00'))->diff(new \DateTime('2026-01-01 00:00:00')), '1:00'];
        $interval = new \DateInterval('PT1H');
        $interval->invert = 1;
        yield 'DateInterval input' => [$interval, '1:00'];
    }

    public function testFormatElapsedTimeWithDateInterval(): void
    {
        $extension = new ElapsedTimeExtension();

        $result = $extension->formatElapsedTime(new \DateInterval('PT30M'));

        self::assertSame('-1:30', $result);
    }
}
