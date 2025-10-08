<?php

namespace App\Controller;

use App\Entity\Board;
use App\Entity\Account;
use App\Form\BoardType;
use App\Form\AddCollaboratorType;
use App\Repository\BoardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/board')]
final class BoardController extends AbstractController
{

    #[Route(name: 'app_board_index', methods: ['GET'])]
    public function index(BoardRepository $boardRepository): Response
    {
        return $this->render('board/index.html.twig', [
            'boards' => $boardRepository->findVisibleForUser($this->getUser()),
        ]);
    }

    #[Route('/new', name: 'app_board_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $board = new Board();
        $form = $this->createForm(BoardType::class, $board);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $board->setOwner($this->getUser());
            $entityManager->persist($board);
            $entityManager->flush();

            return $this->redirectToRoute('app_board_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('board/new.html.twig', [
            'board' => $board,
            'form' => $form,
        ]);
    }

    #[IsGranted('BOARD_EDIT', subject: 'board')]
    #[Route('/{id}/edit', name: 'app_board_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Board $board, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BoardType::class, $board);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_board_index', [], Response::HTTP_SEE_OTHER);
        }

        // Create collaborator form if user is owner
        $collaboratorForm = null;
        if ($this->isGranted('BOARD_MANAGE_COLLABORATORS', $board)) {
            $collaboratorForm = $this->createForm(AddCollaboratorType::class, null, ['board' => $board]);
        }

        return $this->render('board/edit.html.twig', [
            'board' => $board,
            'form' => $form,
            'collaborator_form' => $collaboratorForm?->createView(),
        ]);
    }

    #[IsGranted('BOARD_DELETE', subject: 'board')]
    #[Route('/{id}', name: 'app_board_delete', methods: ['POST'])]
    public function delete(Request $request, Board $board, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $board->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($board);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_board_index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('BOARD_MANAGE_COLLABORATORS', subject: 'board')]
    #[Route('/{id}/collaborator/add', name: 'app_board_add_collaborator', methods: ['POST'])]
    public function addCollaborator(Request $request, Board $board, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AddCollaboratorType::class, null, ['board' => $board]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $collaborator = $em->getRepository(Account::class)->findOneBy(['email' => $email]);

            if (!$collaborator) {
                $this->addFlash('error', 'No user found with this email address.');
                return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
            }

            // Validation: Cannot add owner
            if ($collaborator === $board->getOwner()) {
                $this->addFlash('error', 'Cannot add the owner as a collaborator.');
                return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
            }

            // Validation: Prevent duplicates
            if ($board->getAccounts()->contains($collaborator)) {
                $this->addFlash('warning', 'This user is already a collaborator.');
                return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
            }

            $board->addAccount($collaborator);
            $em->flush();

            $this->addFlash('success', sprintf('%s has been added as a collaborator.', $collaborator->getEmail()));
        }

        return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
    }

    #[IsGranted('BOARD_MANAGE_COLLABORATORS', subject: 'board')]
    #[Route('/{id}/collaborator/{userId}/remove', name: 'app_board_remove_collaborator', methods: ['POST'])]
    public function removeCollaborator(Request $request, Board $board, int $userId, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('remove_collaborator' . $userId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        $collaborator = $em->getRepository(Account::class)->find($userId);

        if (!$collaborator || $collaborator === $board->getOwner()) {
            $this->addFlash('error', 'Invalid user.');
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        $board->removeAccount($collaborator);
        $em->flush();

        $this->addFlash('success', sprintf('%s has been removed.', $collaborator->getEmail()));
        return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
    }
}
