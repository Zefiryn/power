<?php

namespace App\Controller;

use App\Repository\ReadingRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class AnalysisController extends AbstractController
{
    #[Route('/{_locale}/analysis', name: 'analysis')]
    #[Template('analysis/index.html.twig')]
    public function index(
        ChartBuilderInterface $chartBuilder,
        ReadingRepository $readingRepository,
        TranslatorInterface $translator,
        Request $request,
    ): Response {
        $page = $request->query->getInt('page', 1);
        $chartData = $this->prepareData($readingRepository, $page);
        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => array_keys($chartData),
            'datasets' => [
                [
                    'label' => $translator->trans('Analysis.chart.usage'),
                    'backgroundColor' => 'rgb(255, 99, 132, .9)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => array_column($chartData, 'usage'),
                    'order' => 1,
                    'yAxisID' => 'usage',
                ],
                [
                    'label' => $translator->trans('Analysis.chart.usageperhour'),
                    'backgroundColor' => 'rgb(74,103,65, .9)',
                    'borderColor' => 'rgb(74,103,65)',
                    'data' => array_column($chartData, 'hourly'),
                    'type' => 'line',
                    'order' => 0,
                    'yAxisID' => 'hourly',
                ],
            ],
        ]);
        $chart->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'usage' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                ],
                'hourly' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ]);

        return $this->render('analysis/index.html.twig', [
            'chart' => $chart,
            'page' => $page,
            'max' => ceil($readingRepository->recordSummaryCount() / 50),
        ]);
    }

    protected function prepareData(ReadingRepository $readingRepository, int $page): array
    {
        $readings = $readingRepository->findLatestRecords(50, $page)->fetchAllAssociative();

        $chartData = [];
        foreach ($readings as $idx => $reading) {
            if ($idx > 0) {
                $prevReading = $readings[$idx - 1];
                if ($prevReading['device_id'] === $reading['device_id']) {
                    $currentDate = new \DateTime($reading['date']);
                    $prevDate = new \DateTime($readings[$idx - 1]['date']);
                    $interval = $prevDate->diff($currentDate);
                    $daysDiff = (int) $interval->format('%a');
                    if ($daysDiff > 1) {
                        $usage = $reading['usage'] / $daysDiff;
                        $hourly = ($reading['time'] > 0 ? ($reading['usage'] / $reading['time']) * 360 : 0) / $daysDiff;
                        for ($i = 0; $i < $daysDiff; ++$i) {
                            $targetDate = clone $prevDate;
                            if ($i > 0) {
                                $targetDate->modify("-$i day");
                            }
                            $dateStr = $targetDate->format('Y-m-d');
                            $chartData[$dateStr]['usage'] = sprintf('%.1f', $usage / 10);
                            $chartData[$dateStr]['hourly'] = $hourly;
                            continue;
                        }
                    }
                }
            }
            $chartData[$reading['date']]['usage'] = sprintf('%.1f', $reading['usage'] / 10);
            $chartData[$reading['date']]['hourly'] = $reading['time'] > 0 ? ($reading['usage'] / $reading['time']) * 360 : 0;
        }

        return $chartData;
    }
}
