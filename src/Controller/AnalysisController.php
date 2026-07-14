<?php

namespace App\Controller;

use App\Entity\Reading;
use App\Repository\ReadingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route; // Keeping the attribute use as it's standard in newer symfony versions
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class AnalysisController extends AbstractController
{
    #[Route('/{_locale}/analysis', name: 'analysis')]
    public function index(
        ChartBuilderInterface $chartBuilder,
        ReadingRepository $readingRepository,
        TranslatorInterface $translator,
        Request $request,
    ): Response {
        $today = new \DateTime('2026-03-25');
        $endDate = $request->query->get('end_date') ?: $today->format('Y-m-d');

        $startDate = $request->query->get('start_date');
        if (!$startDate) {
            $date = new \DateTime($endDate);
            $date->modify('-30 days');
            $startDate = $date->format('Y-m-d');
        }

        $chartData = $this->prepareData($readingRepository, $startDate, $endDate);
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
                'usage' => ['type' => 'linear', 'display' => true, 'position' => 'left'],
                'hourly' => ['type' => 'linear', 'display' => true, 'position' => 'right', 'grid' => ['drawOnChartArea' => false]],
            ],
        ]);

        return $this->render('analysis/index.html.twig', [
            'chart' => $chart,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'max' => ceil($readingRepository->recordSummaryCount() / 50),
        ]);
    }

    #[Route('/{_locale}/candle', name: 'candle')]
    public function candle(
        ChartBuilderInterface $chartBuilder,
        ReadingRepository $readingRepository,
        TranslatorInterface $translator,
        Request $request
    ): Response {
        $today = new \DateTime('2026-03-25');
        $endDate = $request->query->get('end_date') ?: $today->format('Y-m-d');

        $startDate = $request->query->get('start_date');
        if (!$startDate) {
            $date = new \DateTime($endDate);
            $date->modify('-30 days');
            $startDate = $date->format('Y-m-d');
        }

        $chartData = $this->prepareData($readingRepository, $startDate, $endDate);

        // Group data by day of the week index (Monday=0)
        $dailyGroupedChartData = [];
        $dayOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($chartData as $date => $data) {
            $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
            if ($dateTime) {
                $dayIndex = $dateTime->format('N')-1;
                if (!isset($dailyGroupedChartData[$dayIndex])) {
                    $dailyGroupedChartData[$dayIndex] = [];
                }
                $dailyGroupedChartData[$dayIndex][] = (float)$data['usage'];
            }
        }
        $averagePerDay = [];
        $median = [];
        foreach ($dayOfWeek as $dayId => $dayName) {
            $values = $dailyGroupedChartData[$dayId];
            sort($values);
            $dailyGroupedChartData[$dayName] = [min($values), max($values)];
            $averagePerDay[$dayName] = array_sum($values)/count($values);
            $median[$dayName] = $values[ceil(count($values)/2)];
            unset($dailyGroupedChartData[$dayId]);
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => $dayOfWeek,
            'datasets' => [
                [
                    'label' => $translator->trans('Analysis.chart.range_usage'),
                    'backgroundColor' => '#5e3c99',
                    'borderColor' => '#5e3c99',
                    'data' => $dailyGroupedChartData,
                    'yAxisID' => 'range',
                    'order' => 2,
                ],
                [
                    'label' => $translator->trans('Analysis.chart.average'),
                    'backgroundColor' => '#e66101',
                    'borderColor' => '#e66101',
                    'data' => $averagePerDay,
                    'type' => 'line',
                    'order' => 1,
                    'yAxisID' => 'range',
                ],
                [
                    'label' => $translator->trans('Analysis.chart.median'),
                    'backgroundColor' => '#fdb863',
                    'borderColor' => '#fdb863',
                    'data' => $median,
                    'type' => 'line',
                    'order' => 0,
                    'yAxisID' => 'range',
                ],
            ],
        ]);

        $chart->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'range' => ['type' => 'linear', 'display' => true, 'position' => 'left']
            ],
        ]);

        return $this->render('analysis/candle.html.twig', [
            'chart' => $chart,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'max' => ceil($readingRepository->recordSummaryCount() / 50),
        ]);
    }

    protected function prepareData(ReadingRepository $readingRepository, string $startDate, string $endDate): array
    {
        $readings = $readingRepository->findByDateRange($startDate, $endDate);

        $chartData = [];
        foreach ($readings as $idx => $reading) {
            if ($idx === 0) {
                continue;
            }
            if ($idx > 0) {
                $prevReading = $readings[$idx - 1];
                if ($prevReading->device_id === $reading->device_id) {
                    $currentDate = new \DateTime($reading->full_date);
                    $prevDate = new \DateTime($readings[$idx - 1]->full_date);
                    $interval = $prevDate->diff($currentDate);
                    $daysDiff = (int) $interval->format('%a');
                    if ($daysDiff > 1) {
                        $usage = $reading->usage / $daysDiff;
                        $hourly = ($reading->time > 0 ? (($reading->usage / Reading::DECIMAL_DIVISION) / $reading->time) * 3600 : 0) / $daysDiff;
                        for ($i = 0; $i < $daysDiff; ++$i) {
                            $targetDate = clone $prevDate;
                            if ($i > 0) {
                                $targetDate->modify("-$i day");
                            }
                            $dateStr = $targetDate->format('Y-m-d');
                            $chartData[$dateStr]['usage'] = sprintf('%.1f', $usage / Reading::DECIMAL_DIVISION);
                            $chartData[$dateStr]['hourly'] = $hourly;
                            continue;
                        }
                    }
                }
            }
            $chartData[$reading->date]['usage'] = sprintf('%.1f', $reading->usage / Reading::DECIMAL_DIVISION);
            $chartData[$reading->date]['hourly'] = $reading->time > 0 ? (($reading->usage / Reading::DECIMAL_DIVISION) / $reading->time) * 3600 : 0;
        }

        return $chartData;
    }
}