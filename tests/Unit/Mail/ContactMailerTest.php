<?php

namespace App\Tests\Unit\Mail;

use App\Dto\ContactData;
use App\Service\ContactMailer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

#[CoversClass(ContactMailer::class)]
final class ContactMailerTest extends TestCase
{
    public function testCanCreateContactMailer(): void
    {
        $cm = new ContactMailer(
            $this->createMock(MailerInterface::class),
            $this->createMock(Environment::class),
            'admin@test.com'
        );
        $this->assertInstanceOf(ContactMailer::class, $cm);
    }

    public function testSendContactEmailRendersTemplateAndSends(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $twig   = $this->createMock(Environment::class);

        $data = (new ContactData())
            ->setName('Alice')->setEmail('alice@example.com')
            ->setSubject('Hello')->setMessage('World');

        $twig->expects($this->once())
            ->method('render')
            ->with($this->stringContains('emails/contact.html.twig'), $this->arrayHasKey('contact'))
            ->willReturn('<html>Email</html>');

        $mailer->expects($this->once())->method('send');

        (new ContactMailer($mailer, $twig, 'admin@test.com'))->sendContactEmail($data);

        $this->addToAssertionCount(1);
    }
}
