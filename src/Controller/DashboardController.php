<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Board;
use App\Repository\BoardRepository;


#[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
final class DashboardController extends AbstractController
{
    public function __invoke(BoardRepository $boards): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // Ideally: filter by user's membership
        $userBoards = $boards->findForUser($this->getUser());
        return $this->render('dashboard/index.html.twig', [
            'boards' => $userBoards,
        ]);
    }
}
