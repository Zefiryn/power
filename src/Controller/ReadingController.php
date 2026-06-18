<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\Reading;
use App\Form\ReadingType;
use App\Repository\ReadingRepository;
use App\Utils\PaginatorFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ReadingController extends AbstractController
{
    /**
     * @return array<string, mixed>
     */
    #[Route('/{_locale}/reading', name: 'readings', methods: ['GET'])]
    #[Template('reading/index.html.twig')]
    public function index(
        Request $request,
        ReadingRepository $readingRepository,
        PaginatorFactory $paginatorFactory,
    ): array {
        $paginator = $paginatorFactory->createPaginator();
        $records = $readingRepository->findRecords();
        $paginator->paginate($records, max(1, $request->query->getInt('page', 1)), 21, true);

        return [
            'paginator' => $paginator,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    #[Route('/{_locale}/reading/new', name: 'new_reading', requirements: ['_locale' => '%app.supported_locales_regex%'], methods: ['GET', 'POST'])]
    #[Template('reading/edit.html.twig')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response|array
    {
        $reading = new Reading();
        $reading->setDevice($entityManager->getRepository(Device::class)->findOneBy(['isCurrent' => true]));

        return $this->prepareAndHandleReadingForm($reading, $request, $entityManager);
    }

    /**
     * @return array<string, mixed>
     */
    #[Route('/{_locale}/reading/{id}/edit', name: 'edit_reading', requirements: ['_locale' => '%app.supported_locales_regex%'], methods: ['GET', 'POST'])]
    #[Template('reading/edit.html.twig')]
    public function edit(Request $request, ReadingRepository $readingRepository, EntityManagerInterface $entityManager): Response|array
    {
        if (!$request->get('id')) {
            return $this->redirectToRoute('readings');
        }

        $reading = $readingRepository->find($request->get('id'));
        if (!$reading) {
            return $this->redirectToRoute('readings');
        }

        return $this->prepareAndHandleReadingForm($reading, $request, $entityManager);
    }

    #[Route('/{_locale}/reading/{id}/delete', name: 'remove_reading', requirements: ['_locale' => '%app.supported_locales_regex%'], methods: ['POST'])]
    public function remove(Request $request, ReadingRepository $readingRepository, EntityManagerInterface $entityManager): RedirectResponse
    {
        if (!$request->get('id')) {
            return $this->redirectToRoute('readings');
        }
        $reading = $readingRepository->find($request->get('id'));
        if (!$reading) {
            return $this->redirectToRoute('readings');
        }
        $entityManager->remove($reading);
        $entityManager->flush();

        return $this->redirectToRoute('readings');
    }

    protected function prepareAndHandleReadingForm(Reading $reading, Request $request, EntityManagerInterface $entityManager): array|RedirectResponse
    {
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

        return ['form' => $form, 'is_edit' => (bool) $request->attributes->get('id')];
    }
}
