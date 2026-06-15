<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\DeviceRepository;
use App\Repository\ReadingRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    /**
     * @return array<string, mixed>
     */
    #[Route('/{_locale}/dashboard', name: 'dashboard', requirements: ['_locale' => '%app.supported_locales_regex%'])]
    #[Template('main/dashboard.html.twig')]
    public function dashboard(ReadingRepository $readingRepository): array
    {
        $readings = $readingRepository->findLatestRecords(10)->fetchAllAssociative();
        return [
            'readings' => $readings,
            'summary'  => $this->calculateSummary($readings)
        ];
    }

    /**
     * @param array<int, mixed> $readings
     * @return array<string, double>
     */
    private function calculateSummary(array $readings): array
    {
        usort($readings, function (array $a, array $b) {
            return $a['usage'] <=> $b['usage'];
        });
        $itemCount = count($readings);
        $sum = array_sum(array_column($readings, 'usage'));
        if ($itemCount % 2 === 0) {
            $median = $readings[(int)ceil($itemCount / 2)]['usage'];
        } else {
            $median = ($readings[($itemCount / 2) - 1]['usage'] + $readings[($itemCount / 2) + 1]['usage']) / 2;
        }
        return [
            'avg'    => $sum / $itemCount,
            'median' => $median,
            'sum'    => $sum
        ];
    }
}
