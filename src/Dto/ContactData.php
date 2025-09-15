<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ContactData
{
    #[Assert\NotBlank(message: "Name is required")]
    private string $name = '';

    #[Assert\NotBlank(message: "Email address is required")]
    #[Assert\Email(message: "Invalid email address")]
    #[Assert\Email(mode: 'strict')]
    private string $email = '';

    #[Assert\NotBlank(message: "Subject is required")]
    #[Assert\Length(max: 120, maxMessage: "The subject cannot be longer than {{ limit }} characters.")]
    private string $subject = '';

    #[Assert\NotBlank(message: "Message is required")]
    #[Assert\Length(
        min: 10,
        max: 2000,
        minMessage: "Your message must be at least {{ limit }} characters long.",
        maxMessage: "Your message cannot exceed {{ limit }} characters."
    )]
    private string $message = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = strtolower(trim($email));
        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }
}

