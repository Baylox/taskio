<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Board;

final class DashboardController extends AbstractController
{
    #[Route('/boards/{id}', name: 'app_board_dashboard', methods: ['GET'])]
    public function showBoard(Board $board): Response
    {


        return $this->render('dashboard/board.html.twig', [
            'board' => $board,
        ]);
    }
}
