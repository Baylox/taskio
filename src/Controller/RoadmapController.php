<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RoadmapController extends AbstractController
{
    #[Route('/roadmap', name: 'app_roadmap')]
    public function index(): Response
    {
        return $this->render('roadmap/index.html.twig', [
            'controller_name' => 'RoadmapController',
        ]);
    }
}
