<?php

namespace App\Controller;

use App\Entity\Lane;
use App\Form\LaneType;
use App\Repository\LaneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lane')]
final class LaneController extends AbstractController
{
    #[Route(name: 'app_lane_index', methods: ['GET'])]
    public function index(LaneRepository $laneRepository): Response
    {
        return $this->render('dashboard/lane/index.html.twig', [
            'lanes' => $laneRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_lane_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lane = new Lane();
        $form = $this->createForm(LaneType::class, $lane);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lane);
            $entityManager->flush();

            return $this->redirectToRoute('app_lane_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/lane/new.html.twig', [
            'lane' => $lane,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_lane_show', methods: ['GET'])]
    public function show(Lane $lane): Response
    {
        return $this->render('dashboard/lane/show.html.twig', [
            'lane' => $lane,
        ]);
    }

    #[Route('/{id}', name: 'app_lane_delete', methods: ['POST'])]
    public function delete(Request $request, Lane $lane, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lane->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($lane);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_lane_index', [], Response::HTTP_SEE_OTHER);
    }

    // Todo -> AJAXs
    #[Route('/{id}/edit', name: 'app_lane_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lane $lane, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LaneType::class, $lane);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_lane_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/lane/edit.html.twig', [
            'lane' => $lane,
            'form' => $form,
        ]);
    }
}
