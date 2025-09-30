<?php

namespace App\Controller\Admin;

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
#[Route('admin/account', name: 'admin_account_')]
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

        return $this->render('admin/account/index.html.twig', [
            'accounts'        => $accounts,
            'current_role'    => $role,
            'available_roles' => $availableRoles,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Account $account): Response
    {
        return $this->render('admin/account/show.html.twig', [
            'account' => $account,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Account $account, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AccountType::class, $account);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('admin_account_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/account/edit.html.twig', [
            'account' => $account,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Account $account, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$account->getId(), $request->request->get('_token'))) {
            $em->remove($account);
            $em->flush();
            // Account deleted.
            $this->addFlash('success', 'Account deleted.');
        } else {
            // Invalid CSRF token.
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('admin_account_index', [], Response::HTTP_SEE_OTHER);
    }
}
