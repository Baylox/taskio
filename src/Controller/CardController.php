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
    #[Route(name: 'app_card_index', methods: ['GET'])]
    public function index(CardRepository $cardRepository): Response
    {
        return $this->render('dashboard/card/index.html.twig', [
            'cards' => $cardRepository->findAll(),
        ]);
    }

    #[Route('/lanes/{laneId}/cards', name: 'card_new', methods: ['POST'])]
    public function createForLane(int $laneId, Request $request, EntityManagerInterface $em): Response
    {
        $lane = $em->getRepository(\App\Entity\Lane::class)->find($laneId);
        if (!$lane) {
            throw $this->createNotFoundException('Lane not found');
        }

        // Vérifier les permissions sur le board
        // $this->denyAccessUnlessGranted('EDIT', $lane->getBoard());

        $card = new Card();
        $card->setLane($lane);

        $form = $this->createForm(CardType::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($card);
            $em->flush();

            // PRG: retour propre au dashboard
            return $this->redirectToRoute('app_board_dashboard', ['id' => $lane->getBoard()->getId()], Response::HTTP_SEE_OTHER);
        }

        // Erreurs -> on réaffiche le dashboard avec la modale ouverte et les erreurs
        $board = $lane->getBoard();

        // Recréer le formulaire lane
        $laneForm = $this->createForm(\App\Form\LaneType::class, new \App\Entity\Lane());

        // Recréer les formulaires cards pour toutes les lanes
        $cardForms = [];
        foreach ($board->getLanes() as $boardLane) {
            if ($boardLane->getId() === $lane->getId()) {
                // Pour la lane avec erreur, utiliser le formulaire avec erreurs
                $cardForms[$boardLane->getId()] = $form;
            } else {
                // Pour les autres lanes, créer un nouveau formulaire
                $newCard = new Card();
                $newCard->setLane($boardLane);
                $cardForms[$boardLane->getId()] = $this->createForm(CardType::class, $newCard);
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'board' => $board,
            'laneForm' => $laneForm->createView(),
            'cardForms' => array_map(fn($form) => $form->createView(), $cardForms),
            'openCardModalId' => $lane->getId(), // Ouvrir la modale avec erreurs
        ]);
    }

    #[Route('/new', name: 'app_card_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $card = new Card();
        $form = $this->createForm(CardType::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($card);
            $entityManager->flush();

            return $this->redirectToRoute('app_card_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/card/new.html.twig', [
            'card' => $card,
            'form' => $form,
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
        if ($this->isCsrfTokenValid('delete'.$card->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($card);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_card_index', [], Response::HTTP_SEE_OTHER);
    }

    //Todo : AJAX
    #[Route('/{id}/edit', name: 'app_card_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Card $card, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CardType::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_card_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/card/edit.html.twig', [
            'card' => $card,
            'form' => $form,
        ]);
    }
}
