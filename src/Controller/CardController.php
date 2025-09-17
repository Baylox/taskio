<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Lane;
use App\Form\CardType;
use App\Form\LaneType;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/card')]
final class CardController extends AbstractController
{
    #[Route('/lanes/{laneId}/cards', name: 'card_new', methods: ['POST'])]
public function createForLane(
    #[MapEntity(mapping: ['laneId' => 'id'])] Lane $lane,
    Request $request,
    EntityManagerInterface $em,
    CardRepository $cardRepo
): Response {

    // $this->denyAccessUnlessGranted('EDIT', $lane->getBoard());

    $card = new Card();
    $card->setLane($lane);

    // Position at the bottom of the lane (robust if lane is empty)
    $maxPosition = $cardRepo->findMaxPositionInLane($lane);
    $card->setPosition(($maxPosition ?? 0) + 1);

    $form = $this->createForm(CardType::class, $card);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($card);
        $em->flush();

        return $this->redirectToRoute(
            'app_board_dashboard',
            ['id' => $lane->getBoard()->getId()],
            Response::HTTP_SEE_OTHER
        );
    }
    // Invalid case: return only the strict minimum
    return $this->render('dashboard/index.html.twig', [
        'board' => $lane->getBoard(),
        'laneForm' => $this->createForm(LaneType::class, new Lane())->createView(),
        'cardFormForLane' => [$lane->getId() => $form->createView()],
        'openCardModalLaneId' => $lane->getId(),
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
            'laneForm'        => $this->createForm(LaneType::class)->createView(),
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
            $forms[$lane->getId()] = $this->createForm(LaneType::class, $lane)->createView();
        }
        return $forms;
    }
}
