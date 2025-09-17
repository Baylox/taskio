<?php

namespace App\Controller;

use App\Entity\Board;
use App\Entity\Card;
use App\Entity\Lane;
use App\Form\CardType;
use App\Form\LaneType;
use App\Repository\BoardRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


final class DashboardController extends AbstractController
{
    // EDIT of board
    #[Route('/boards/{id<\d+>}', name: 'app_board_dashboard', methods: ['GET'])]
    public function index(int $id, BoardRepository $boards): Response
    {
        $board = $boards->findWithLanesAndCards($id);
        if (!$board) {
            throw $this->createNotFoundException('Board not found');
        }

        // Créer le formulaire pour les lanes
        $laneForm = $this->createForm(LaneType::class, new Lane());

        // Créer les formulaires pour les cards (un par lane)
        $cardForms = [];
        foreach ($board->getLanes() as $lane) {
            $card = new Card();
            $card->setLane($lane); // Pré-assigner la lane
            $cardForms[$lane->getId()] = $this->createForm(CardType::class, $card);
        }

        return $this->render('dashboard/index.html.twig', [
            'board' => $board,
            'laneForm' => $laneForm->createView(),
            'cardForms' => array_map(fn($form) => $form->createView(), $cardForms),
            'openLaneModal' => false,
        ]);
    }
}
