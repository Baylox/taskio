<?php

namespace App\Controller;

use App\Entity\Lane;
use App\Entity\Board;
use App\Form\LaneType;
use App\Repository\LaneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

    // PRG from the dashboard (adding a lane)
    // The Board is received in the URL to know where to attach the lane
    #[Route('/boards/{id}/lanes', name: 'lane_new', methods: ['POST'])]
    public function create(Board $board, Request $request, EntityManagerInterface $em): Response
    {
        // (Optionnel mais recommandé)
        $this->denyAccessUnlessGranted('EDIT', $board);

        $lane = new Lane();
        $lane->setBoard($board);

        $form = $this->createForm(LaneType::class, $lane);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lane);
            $em->flush();

            // PRG: retour propre au dashboard
            return $this->redirectToRoute('app_board_dashboard', ['id' => $board->getId()], Response::HTTP_SEE_OTHER);
        }

        // Erreurs -> on réaffiche le dashboard avec la modale ouverte et les erreurs
        return $this->render('dashboard/index.html.twig', [
            'board'         => $board,
            'laneForm'      => $form->createView(),
            'openLaneModal' => true,
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
