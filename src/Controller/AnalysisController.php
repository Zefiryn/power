<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AnalysisController extends AbstractController
{
    #[Route('/{_locale}/analysis', name: 'analysis')]
    #[Template('analysis/index.html.twig')]
    public function index(): Response|array
    {
        return [];
    }
}
