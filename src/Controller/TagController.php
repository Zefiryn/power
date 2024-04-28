<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TagController extends AbstractController
{
    #[Route('/{_locale}/tag', name: 'tags')]
    #[Template('tag/index.html.twig')]
    public function index(): Response|array
    {
        return [];
    }
}
