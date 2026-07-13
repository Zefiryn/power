<?php

declare(strict_types=1);

namespace App\Tests\Integration\Twig;

use App\Twig\ElapsedTimeExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

class ElapsedTimeExtensionIntegrationTest extends KernelTestCase
{
    public function testExtensionIsRegisteredAndWorksInTwig(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var Environment $twig */
        $twig = $container->get(Environment::class);

        // Verify the extension is registered
        $extension = $twig->getExtension(ElapsedTimeExtension::class);
        self::assertInstanceOf(ElapsedTimeExtension::class, $extension);

        // Verify the filter works in a template
        $template = '{{ 3661|elapseTime }}';
        $result = $twig->createTemplate($template)->render();

        self::assertSame('1:02', $result);
    }

    /**
     * @dataProvider provideTestData
     */
    public function testFilterWithDifferentInputs(int|string|\DateInterval $input, string $expected): void
    {
        self::bootKernel();
        $twig = self::getContainer()->get(Environment::class);

        $template = '{{ input|elapseTime }}';
        $result = $twig->createTemplate($template)->render(['input' => $input]);

        self::assertSame($expected, $result);
    }

    public function provideTestData(): iterable
    {
        yield 'seconds as int' => [3600, '1:00'];
        yield 'seconds as string' => ['60', '0:01'];
        yield 'DateInterval' => [new \DateInterval('PT1H2M3S'), '1:03'];
    }
}
