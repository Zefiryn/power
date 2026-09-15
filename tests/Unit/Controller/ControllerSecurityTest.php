<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\AnalysisController;
use App\Controller\DashboardController;
use App\Controller\DeviceController;
use App\Controller\MainController;
use App\Controller\ReadingController;
use App\Controller\SettingsController;
use App\Controller\TagController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ControllerSecurityTest extends TestCase
{
    /**
     * Controllers that must require ROLE_USER (class-level #[IsGranted] attribute).
     *
     * @return array<string, array{class-string}>
     */
    public static function roleUserControllerProvider(): array
    {
        return [
            'AnalysisController' => [AnalysisController::class],
            'DashboardController' => [DashboardController::class],
            'DeviceController' => [DeviceController::class],
            'ReadingController' => [ReadingController::class],
            'SettingsController' => [SettingsController::class],
            'TagController' => [TagController::class],
        ];
    }

    /**
     * Controllers that must remain open (public login page).
     *
     * @return array<string, array{class-string}>
     */
    public static function openControllerProvider(): array
    {
        return [
            'MainController' => [MainController::class],
        ];
    }

    /**
     * @dataProvider roleUserControllerProvider
     */
    public function testRoleUserControllersRequireRoleUser(string $controllerClass): void
    {
        $isGranted = $this->classLevelAttribute($controllerClass, IsGranted::class);
        self::assertNotNull(
            $isGranted,
            sprintf('%s is missing a class-level #[IsGranted] attribute.', $controllerClass),
        );
        self::assertSame(
            'ROLE_USER',
            $isGranted->attribute,
            sprintf('%s class-level #[IsGranted] must reference ROLE_USER.', $controllerClass),
        );
    }

    /**
     * @dataProvider openControllerProvider
     */
    public function testOpenControllersHaveNoClassLevelIsGranted(string $controllerClass): void
    {
        self::assertNull(
            $this->classLevelAttribute($controllerClass, IsGranted::class),
            sprintf('%s must not have a class-level #[IsGranted] attribute.', $controllerClass),
        );
    }

    private function classLevelAttribute(string $className, string $attributeClass): ?object
    {
        foreach ((new \ReflectionClass($className))->getAttributes($attributeClass) as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }
}
