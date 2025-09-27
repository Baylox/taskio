<?php

namespace App\Controller\Admin;

use App\Repository\BoardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/board', name: 'admin_board_')]
#[IsGranted('ROLE_ADMIN')]
final class BoardController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(BoardRepository $boards): Response
    {
        return $this->render('board/index.html.twig', [
            'boards'    => $boards->findAllForAdmin(),
        ]);
    }
}

