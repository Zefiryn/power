<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use App\Utils\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TagController extends AbstractController
{
    #[Route('/{_locale}/tag', name: 'tags')]
    #[Template('tag/index.html.twig')]
    public function index(
        Request $request,
        TagRepository $tagRepository,
        Paginator $paginator
    ): Response|array
    {
        $records = $tagRepository->findRecords();
        $paginator->paginate($records, max(1, $request->query->getInt('page', 1)), 50);

        return [
            'paginator' => $paginator
        ];
    }

    #[Route('/{_locale}/tag/new', name: 'new_tag', requirements: ['_locale' => '%app.supported_locales_regex%'])]
    #[Template('tag/edit.html.twig')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response|array
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tag = $form->getData();
            $entityManager->persist($tag);
            $entityManager->flush();

            return $this->redirectToRoute('tags');
        }

        return ['form' => $form];
    }
}
