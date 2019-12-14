<?php
namespace App\Twig;

use DateInterval;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TimeExtension extends AbstractExtension
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('ago', [$this, 'ago'], ['is_safe' => ['html']]),
            new TwigFilter('duration', [$this, 'duration'], ['is_safe' => ['html']])
        ];
    }

    public function ago(\DateTime $date, string $format = 'd/m/Y H:i')
    {
        return '<span class="timeago" datetime="' . $date->format(\DateTime::ISO8601) . '">' .
        $date->format($format) .
        '</span>';
    }

    public function duration($date, string $format = '%im %s')
    {
        return '<span>' .
        (new DateInterval($date))->format($format) .
        '</span>';
    }
}