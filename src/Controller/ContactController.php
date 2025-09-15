<?php

namespace App\Controller;

use App\Dto\ContactData;
use App\Form\ContactType;
use App\Service\ContactMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, ContactMailer $contactMailer): Response
    {
        $contactData = new ContactData();
        $form = $this->createForm(ContactType::class, $contactData);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Check honeypot
            if ($form->get('website')->getData()) {
                // Spam detected, reject silently
                return $this->redirectToRoute('app_contact');
            }
            
            try {
                $contactMailer->sendContactEmail($contactData);
                $this->addFlash('success', 'Your message has been sent successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while sending your message. Please try again.');
            }
            
            return $this->redirectToRoute('app_contact');
        }
        
        return $this->render('pages/contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
