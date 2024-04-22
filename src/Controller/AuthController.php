<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\LocaleSwitcher;

class AuthController extends AbstractController
{
    private LocaleSwitcher $localeSwitcher;

    public function __construct(LocaleSwitcher $localeSwitcher)
    {
        $this->localeSwitcher = $localeSwitcher;
    }

    #[Route('/{_locale}/login', name: 'login', requirements: ['_locale' => '%app.supported_locales_regex%'])]
    public function login(): Response
    {
        $params = ['_locale' => $this->localeSwitcher->getLocale()];
        return $this->redirectToRoute('dashboard', $params);
    }
}
