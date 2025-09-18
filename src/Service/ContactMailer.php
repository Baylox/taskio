<?php

namespace App\Service;

use App\Dto\ContactData;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class ContactMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private string $adminEmail
    ) {
    }

    public function sendContactEmail(ContactData $contactData): void
    {
        // Sanitize subject (avoid header injection)
        $subject = trim(str_replace(["\r", "\n"], '', $contactData->getSubject()));

        $email = (new Email())
            ->from('no-reply@taskio.com')
            ->replyTo($contactData->getEmail())
            ->to($this->adminEmail)
            ->subject('Contact: ' . $subject)
            ->html($this->twig->render('emails/contact.html.twig', [
                'contact' => $contactData,
            ]))
            ->text(sprintf(
                "From: %s <%s>\n\n%s",
                $contactData->getName(),
                $contactData->getEmail(),
                $contactData->getMessage()
            ));

        $this->mailer->send($email);
    }
}
