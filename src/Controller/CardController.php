<?php

namespace App\Controller;

use App\Entity\Card;
use App\Form\CardType;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/card')]
final class CardController extends AbstractController
{
    #[Route('/lanes/{laneId}/cards', name: 'card_new', methods: ['POST'])]
    public function createForLane(int $laneId, Request $request, EntityManagerInterface $em): Response
    {
        $lane = $em->getRepository(\App\Entity\Lane::class)->find($laneId);
        if (!$lane) {
            throw $this->createNotFoundException('Lane not found');
        }

        // Check permissions on the board
        // $this->denyAccessUnlessGranted('EDIT', $lane->getBoard());

        $card = new Card();
        $card->setLane($lane);

        // Set the position at the bottom of the lane
        $maxPosition = $em->getRepository(Card::class)->findMaxPositionInLane($lane);
        $card->setPosition($maxPosition + 1);

        $form = $this->createForm(CardType::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($card);
            $em->flush();

            // PRG: clean redirect to the dashboard
            return $this->redirectToRoute('app_board_dashboard', ['id' => $lane->getBoard()->getId()], Response::HTTP_SEE_OTHER);
        }

        // Errors -> redisplay the dashboard with the modal open and errors shown
        $board = $lane->getBoard();

        // Recreate the lane form
        $laneForm = $this->createForm(\App\Form\LaneType::class, new \App\Entity\Lane());

        // Recreate card forms for all lanes
        $cardForms = [];
        foreach ($board->getLanes() as $boardLane) {
            if ($boardLane->getId() === $lane->getId()) {
            // For the lane with errors, use the form with errors
            $cardForms[$boardLane->getId()] = $form;
            } else {
            // For other lanes, create a new form
            $newCard = new Card();
            $newCard->setLane($boardLane);
            $cardForms[$boardLane->getId()] = $this->createForm(CardType::class, $newCard);
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'board' => $board,
            'laneForm' => $laneForm->createView(),
            'cardForms' => array_map(fn($form) => $form->createView(), $cardForms),
            'openCardModalId' => $lane->getId(), // Open the modal with errors
        ]);
    }

    #[Route('/{id}', name: 'app_card_show', methods: ['GET'])]
    public function show(Card $card): Response
    {
        return $this->render('dashboard/card/show.html.twig', [
            'card' => $card,
        ]);
    }

    #[Route('/{id}', name: 'app_card_delete', methods: ['POST'])]
    public function delete(Request $request, Card $card, EntityManagerInterface $entityManager): Response
    {
        $board = $card->getLane()->getBoard();

        if ($this->isCsrfTokenValid('delete'.$card->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($card);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_board_dashboard', ['id' => $board->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/cards/{id}/edit', name: 'card_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Card $card, EntityManagerInterface $em): Response
    {
        $lane = $card->getLane();
        $board = $lane->getBoard();
        //$this->denyAccessUnlessGranted('EDIT', $board);

        $form = $this->createForm(CardType::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_board_dashboard', ['id' => $board->getId()]);
        }

        // Errors → redisplay dashboard with the UPDATE modal for THIS card open
        $cardEditForms = [];
        foreach ($board->getLanes() as $boardLane) {
            foreach ($boardLane->getCards() as $boardCard) {
                $cardEditForms[$boardCard->getId()] = $boardCard->getId() === $card->getId()
                    ? $form->createView() // keep errors
                    : $this->createForm(CardType::class, $boardCard)->createView();
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'board'           => $board,
            'laneForm'        => $this->createForm(\App\Form\LaneType::class)->createView(),
            'laneEditForms'   => $this->buildLaneEditForms($board),
            'cardEditForms'   => $cardEditForms,
            'openEditCardId'  => $card->getId(),
        ]);
    }

    /** @return array<int, \Symfony\Component\Form\FormView> */
    private function buildLaneEditForms(\App\Entity\Board $board): array
    {
        $forms = [];
        foreach ($board->getLanes() as $lane) {
            $forms[$lane->getId()] = $this->createForm(\App\Form\LaneType::class, $lane)->createView();
        }
        return $forms;
    }
}
