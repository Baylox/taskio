<?php

namespace App\Controller;

use App\Entity\Lane;
use App\Entity\Board;
use App\Form\LaneType;
use App\Repository\LaneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
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
    #[Route('/boards/{id}/lanes/new', name: 'lane_new', methods: ['POST', 'GET'])]
    public function new(Board $board, Request $request, EntityManagerInterface $em, LaneRepository $repo): Response
    {

        $lane = new Lane();
        $lane->setBoard($board);
        $lane->setPosition($repo->getNextPositionForBoard($board)); // ← clé

        $form = $this->createForm(LaneType::class, $lane);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lane);
            $em->flush();
            return $this->redirectToRoute('app_board_dashboard', ['id' => $board->getId()]);
        }

        return $this->render('dashboard/index.html.twig', [
            'board'         => $board,
            'laneForm'      => $form->createView(),
            'laneEditForms' => $this->buildLaneEditForms($board),
            'openLaneModal' => $form->isSubmitted(),
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
        $board = $lane->getBoard();

        if ($this->isCsrfTokenValid('delete' . $lane->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($lane);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_board_dashboard', ['id' => $board->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/lanes/{id}/edit', name: 'lane_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lane $lane, EntityManagerInterface $em): Response
    {
        $board = $lane->getBoard();
        //$this->denyAccessUnlessGranted('EDIT', $board);

        $form = $this->createForm(LaneType::class, $lane);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_board_dashboard', ['id' => $board->getId()]);
        }

        // Errors → redisplay dashboard with the UPDATE modal for THIS lane open
        $laneEditForms = [];
        foreach ($board->getLanes() as $ln) {
            $laneEditForms[$ln->getId()] = $ln->getId() === $lane->getId()
                ? $form->createView() // keep errors
                : $this->createForm(LaneType::class, $ln)->createView();
        }

        return $this->render('dashboard/index.html.twig', [
        'board'           => $board,
        'laneForm'        => $this->createForm(LaneType::class)->createView(),
        'laneEditForms'   => $this->buildLaneEditForms($board, $form, $lane),
        'openEditLaneId'  => $lane->getId(),
        ]);
    }

    /** @return array<int*/
    private function buildLaneEditForms(Board $board, ?FormInterface $currentForm = null, ?Lane $currentLane = null): array
    {
        $forms = [];
        foreach ($board->getLanes() as $ln) {
            if ($currentLane && $ln->getId() === $currentLane->getId() && $currentForm) {
                $forms[$ln->getId()] = $currentForm->createView();
            } else {
                $forms[$ln->getId()] = $this->createForm(LaneType::class, $ln)->createView();
            }
        }
        return $forms;
    }
}
