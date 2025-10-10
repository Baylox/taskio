<?php

namespace App\Controller\Admin;

use App\Repository\BoardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/admin/board', name: 'admin_board_')]
#[IsGranted('ROLE_ADMIN')]
final class BoardController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(BoardRepository $boardRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $search = $request->query->get('search');

        $qb = $boardRepository->qbForAdmin($search);
        $boards = $paginator->paginate($qb, $request->query->getInt('page', 1), 10);

        return $this->render('board/index.html.twig', [
            'boards'         => $boards,
            'current_search' => $search,
            'adminMode'      => true,
        ]);
    }
}

