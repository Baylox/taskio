<?php

namespace App\Tests\Unit\Mail;

use App\Security\EmailVerifier;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

#[CoversClass(EmailVerifier::class)]
class EmailVerifierTest extends TestCase
{
    public function testEmailVerifierExists(): void
    {
        $this->assertTrue(class_exists(EmailVerifier::class));
    }

    public function testCanCreateEmailVerifier(): void
    {
        $verifyHelper = $this->createMock(VerifyEmailHelperInterface::class);
        $mailer = $this->createMock(MailerInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $emailVerifier = new EmailVerifier($verifyHelper, $mailer, $entityManager);

        $this->assertInstanceOf(EmailVerifier::class, $emailVerifier);
    }
}
