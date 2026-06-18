<?php

namespace App\Controller;

use App\Entity\Device;
use App\Form\DeviceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeviceController extends AbstractController
{
    /**
     * @return Response|array<string, mixed>
     */
    #[Route('/{_locale}/device/new', name: 'new_device', requirements: ['_locale' => '%app.supported_locales_regex%'])]
    #[Template('device/edit.html.twig')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response|array
    {
        $device = new Device();
        $form = $this->createForm(DeviceType::class, $device);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $device = $form->getData();
            $entityManager->persist($device);
            $entityManager->flush();
            if ($device->isCurrent()) {
                $entityManager->getRepository(Device::class)->resetCurrentDevices($device->getId());
            }

            return $this->redirectToRoute('settings');
        }

        return ['form' => $form];
    }
}
