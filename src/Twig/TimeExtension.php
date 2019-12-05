<?php
namespace App\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;

class TimeExtension extends AbstractExtension
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }
}