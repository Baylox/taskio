<?php

namespace App\Controller;

use App\Entity\Board;
use App\Entity\Account;
use App\Entity\BoardInvitation;
use App\Form\BoardType;
use App\Form\AddCollaboratorType;
use App\Repository\BoardRepository;
use App\Repository\BoardInvitationRepository;
use App\Service\BoardInvitationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Psr\Log\LoggerInterface;
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
    public function edit(
        Request $request,
        Board $board,
        EntityManagerInterface $entityManager,
        BoardInvitationRepository $invitationRepository
    ): Response
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
            'collaborator_form' => $this->createCollaboratorForm($board),
            'pending_invitations' => $this->getPendingInvitations($board, $invitationRepository),
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
    #[Route('/{id}/collaborator/invite', name: 'app_board_invite_collaborator', methods: ['POST'])]
    public function inviteCollaborator(
        Request $request,
        Board $board,
        RateLimiterFactory $addCollaboratorLimiter,
        BoardInvitationService $invitationService,
        LoggerInterface $logger
    ): Response
    {
        if (!$this->checkRateLimit($addCollaboratorLimiter)) {
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        $form = $this->createForm(AddCollaboratorType::class, null, ['board' => $board]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            if (!$invitationService->canInvite($email, $board)) {
                $this->addFlash('success', 'An invitation has been sent to this email address.');
                return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
            }

            try {
                $invitation = $invitationService->createInvitation($email, $board, $this->getUser());
                $invitationService->sendInvitationEmail($invitation);
                $this->addFlash('success', 'An invitation has been sent to this email address.');
            } catch (\Exception $e) {
                $logger->error('Failed to process invitation', [
                    'error' => $e->getMessage(),
                    'board_id' => $board->getId(),
                    'email' => $email
                ]);
                $this->addFlash('success', 'An invitation has been sent to this email address.');
            }
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

        // IDOR Protection: Verify user can be removed
        if (!$this->canRemoveCollaborator($board, $collaborator)) {
            $this->addFlash('error', 'Invalid user.');
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        $board->removeAccount($collaborator);
        $em->flush();

        $this->addFlash('success', 'Collaborator removed successfully.');
        return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
    }

    #[Route('/invitation/{token}/accept', name: 'app_board_accept_invitation', methods: ['GET'])]
    public function acceptInvitation(
        string $token,
        BoardInvitationRepository $invitationRepository,
        BoardInvitationService $invitationService
    ): Response
    {
        $invitation = $invitationRepository->findValidByToken($token);

        if (!$invitation) {
            $this->addFlash('error', 'This invitation is invalid or has expired.');
            return $this->redirectToRoute('app_board_index');
        }

        $user = $this->getUser();

        if (!$this->validateInvitationForUser($invitation, $user)) {
            return $this->redirectToRoute('app_board_index');
        }

        $board = $invitation->getBoard();

        if ($board->getAccounts()->contains($user) || $board->getOwner() === $user) {
            $invitationService->acceptInvitation($invitation, $user);
            $this->addFlash('info', 'You are already a member of this board.');
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        $invitationService->acceptInvitation($invitation, $user);
        $this->addFlash('success', sprintf('You have successfully joined the board "%s".', $board->getTitle()));

        return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
    }

    #[IsGranted('BOARD_MANAGE_COLLABORATORS', subject: 'board')]
    #[Route('/{id}/invitation/{invitationId}/cancel', name: 'app_board_cancel_invitation', methods: ['POST'])]
    public function cancelInvitation(
        Request $request,
        Board $board,
        int $invitationId,
        BoardInvitationRepository $invitationRepository,
        BoardInvitationService $invitationService
    ): Response
    {
        if (!$this->isCsrfTokenValid('cancel_invitation' . $invitationId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        $invitation = $invitationRepository->find($invitationId);

        if (!$invitation || $invitation->getBoard()->getId() !== $board->getId()) {
            $this->addFlash('error', 'Invalid invitation.');
            return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
        }

        $invitationService->cancelInvitation($invitation);
        $this->addFlash('success', 'Invitation cancelled.');

        return $this->redirectToRoute('app_board_edit', ['id' => $board->getId()]);
    }

    private function checkRateLimit(RateLimiterFactory $limiterFactory): bool
    {
        $limiter = $limiterFactory->create($this->getUser()->getUserIdentifier());

        if (!$limiter->consume(1)->isAccepted()) {
            $this->addFlash('error', 'Too many attempts. Please try again later.');
            return false;
        }

        return true;
    }

    private function createCollaboratorForm(Board $board): ?\Symfony\Component\Form\FormView
    {
        if (!$this->isGranted('BOARD_MANAGE_COLLABORATORS', $board)) {
            return null;
        }

        return $this->createForm(AddCollaboratorType::class, null, ['board' => $board])->createView();
    }

    private function getPendingInvitations(Board $board, BoardInvitationRepository $repository): array
    {
        if (!$this->isGranted('BOARD_MANAGE_COLLABORATORS', $board)) {
            return [];
        }

        return $repository->findPendingByBoard($board);
    }

    private function validateInvitationForUser(BoardInvitation $invitation, Account $user): bool
    {
        if ($user->getEmail() !== $invitation->getEmail()) {
            $this->addFlash('error', 'This invitation was sent to a different email address.');
            return false;
        }

        return true;
    }

    private function canRemoveCollaborator(Board $board, ?Account $collaborator): bool
    {
        if (!$collaborator) {
            return false;
        }

        if ($collaborator === $board->getOwner()) {
            return false;
        }

        if (!$board->getAccounts()->contains($collaborator)) {
            return false;
        }

        return true;
    }
}
