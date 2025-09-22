<?php

namespace App\Tests\Unit\Mail;

use App\Security\EmailVerifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmailVerifier::class)]
class EmailVerifierTest extends TestCase
{
    public function testEmailVerifierExists(): void
    {
        $this->assertTrue(class_exists(EmailVerifier::class));
    }

    public function testCanCreateEmailVerifier(): void
    {
        $verifyHelper = $this->createMock(\SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface::class);
        $mailer = $this->createMock(\Symfony\Component\Mailer\MailerInterface::class);
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);

        $emailVerifier = new EmailVerifier($verifyHelper, $mailer, $entityManager);

        $this->assertInstanceOf(EmailVerifier::class, $emailVerifier);
    }
}
