<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/board', name: 'admin_board_')]
#[IsGranted('ROLE_ADMIN')]
final class BoardController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/board/index.html.twig', [
            'controller_name' => 'Admin/BoardController',
        ]);
    }
}

