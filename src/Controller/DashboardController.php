<?php

namespace App\Controller;

use App\Entity\Board;
use App\Repository\BoardRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


final class DashboardController extends AbstractController
{
    // EDIT of board
    #[Route('/boards/{id<\d+>}', name: 'app_dash', methods: ['GET'])]
    public function index(int $id, BoardRepository $boards): Response
    {
        $board = $boards->findWithLanesAndCards($id);
        if (!$board) {
            throw $this->createNotFoundException('Board not found');
        }

        return $this->render('dashboard/index.html.twig', ['board' => $board]);
    }
}
