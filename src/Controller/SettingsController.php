<?php

namespace App\Controller;

use App\Repository\DeviceRepository;
use App\Repository\TagRepository;
use App\Utils\PaginatorFactory;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends AbstractController
{
    /**
     * @return Response|array<string, mixed>
     */
    #[Route('/{_locale}/settings', name: 'settings')]
    #[Template('settings/index.html.twig')]
    public function index(
        Request $request,
        TagRepository $tagRepository,
        DeviceRepository $deviceRepository,
        PaginatorFactory $paginatorFactory,
    ): Response|array {
        $tagsPaginator = $paginatorFactory->createPaginator();
        $devicePaginator = $paginatorFactory->createPaginator();

        $records = $tagRepository->findRecords();
        $tagsPaginator->paginate($records, max(1, $request->query->getInt('page', 1)), 50);

        $devices = $deviceRepository->findDevices();
        $devicePaginator->paginate($devices, max(1, $request->query->getInt('page', 1)), 50);

        return [
            'tags' => $tagsPaginator,
            'devices' => $devicePaginator,
        ];
    }
}
