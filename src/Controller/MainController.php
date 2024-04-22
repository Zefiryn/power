<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Translation\LocaleSwitcher;

class MainController extends AbstractController
{
    #[Route('/', name: 'default_homepage')]
    #[Route('/{_locale}', name: 'homepage', requirements: ['_locale' => 'en|pl'])]
    #[Template('main/index.html.twig')]
    public function index(): array
    {
        return [];
    }
}
