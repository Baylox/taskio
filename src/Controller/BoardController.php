<?php

namespace App\Controller;

use App\Entity\Board;
use App\Entity\Account;
use App\Form\BoardType;
use App\Repository\BoardRepository;
use App\Repository\AccountRepository;
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
    public function edit(Request $request, Board $board, EntityManagerInterface $entityManager, AccountRepository $accountRepository): Response
    {
        $form = $this->createForm(BoardType::class, $board);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_board_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('board/edit.html.twig', [
            'board' => $board,
            'form' => $form,
            'all_users' => $accountRepository->findAll(),
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
    public function addCollaborator(
        Request $request,
        Board $board,
        AccountRepository $accountRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // CSRF validation
        if (!$this->isCsrfTokenValid('add_collaborator' . $board->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        $userId = $request->request->get('user_id');
        $collaborator = $accountRepository->find($userId);

        if (!$collaborator) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        // Validation: Cannot add owner as collaborator
        if ($collaborator->getId() === $board->getOwner()->getId()) {
            $this->addFlash('error', 'Cannot add the owner as a collaborator.');
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        // Validation: Prevent duplicates
        if ($board->getAccounts()->contains($collaborator)) {
            $this->addFlash('warning', 'User is already a collaborator.');
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        $board->addAccount($collaborator);
        $entityManager->flush();

        $this->addFlash('success', sprintf('%s has been added as a collaborator.', $collaborator->getEmail()));
        return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
    }

    #[IsGranted('BOARD_MANAGE_COLLABORATORS', subject: 'board')]
    #[Route('/{id}/collaborator/{userId}/remove', name: 'app_board_remove_collaborator', methods: ['POST'])]
    public function removeCollaborator(
        Request $request,
        Board $board,
        int $userId,
        AccountRepository $accountRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // CSRF validation
        if (!$this->isCsrfTokenValid('remove_collaborator' . $userId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        $collaborator = $accountRepository->find($userId);

        if (!$collaborator) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        // Validation: Cannot remove owner
        if ($collaborator->getId() === $board->getOwner()->getId()) {
            $this->addFlash('error', 'Cannot remove the owner.');
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        $board->removeAccount($collaborator);
        $entityManager->flush();

        $this->addFlash('success', sprintf('%s has been removed from collaborators.', $collaborator->getEmail()));
        return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
    }
}
