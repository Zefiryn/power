<?php

namespace App\Controller;

use App\Entity\Reading;
use App\Form\ReadingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_USER')]
class ReadingController extends AbstractController
{
    #[Route('/{_locale}/reading', name: 'readings')]
    #[Template('reading/index.html.twig')]
    public function index(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): array
    {
        return [];
    }

    #[Route('/{_locale}/reading/new', name: 'new_reading', requirements: ['_locale' => '%app.supported_locales_regex%'])]
    #[Template('reading/edit.html.twig')]
    public function create(Request $request, EntityManagerInterface $entityManager): mixed
    {
        $reading = new Reading();
        $form = $this->createForm(ReadingType::class, $reading);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reading = $form->getData();
            if (!$reading->getDate()) {
                $reading->setDate(new \DateTime());
            }
            $entityManager->persist($reading);
            $entityManager->flush();

            return $this->redirectToRoute('dashboard');
        }

        return ['form' => $form];
    }
}
