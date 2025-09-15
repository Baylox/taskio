<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\AccountType;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
#[Route('/account', name: 'app_account_')]
final class AccountController extends AbstractController
{
    #[Route(name: 'index', methods: ['GET'])]
    public function index(AccountRepository $accountRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $availableRoles = $accountRepository->distinctRoles();
        $role = $request->query->get('role');

        // if the passed value does not exist in the database, we remove the filter
        if ($role && !in_array($role, $availableRoles, true)) {
            $role = null;
        }

        $qb = $accountRepository->qbByRole($role);
        $accounts = $paginator->paginate($qb, $request->query->getInt('page', 1), 10);

        return $this->render('account/index.html.twig', [
            'accounts'        => $accounts,
            'current_role'    => $role,
            'available_roles' => $availableRoles,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Account $account, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $account->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($account);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_account_index', [], Response::HTTP_SEE_OTHER);
    }
}
