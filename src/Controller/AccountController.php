<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_USER')]
#[Route('/account', name: 'app_account_')]
final class AccountController extends AbstractController
{
    #[Route('/edit', name: 'edit', methods: ['GET','POST'])]
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Account) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateUserPassword($user, $form, $passwordHasher);
            $em->flush();
            $this->addFlash('success', 'Profile updated successfully.');

            return $this->redirectToRoute('app_account_edit', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form,
        ]);
    }

    private function updateUserPassword(Account $user, $form, UserPasswordHasherInterface $passwordHasher): void
    {
        $plainPassword = $form->get('plainPassword')->getData();

        if (!$plainPassword) {
            return;
        }

        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
    }
}

