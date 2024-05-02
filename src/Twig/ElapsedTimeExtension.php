<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ElapsedTimeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('elapseTime', [$this, 'formatElapsedTime']),
        ];
    }

    public function formatElapsedTime($seconds): string
    {
        if ($seconds instanceof \DateInterval) {
            $reference = new \DateTimeImmutable();
            $endTime = $reference->add($seconds);
            $seconds = $reference->getTimestamp() - $endTime->getTimestamp();
        }
        $hours = floor($seconds / 3600);
        $minutes = ceil($seconds - ($hours * 3600)) / 60;

        return implode(':', [$hours, $minutes]);
    }
}
