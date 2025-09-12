<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ContactData
{
    #[Assert\NotBlank(message: "Name is required")]
    public string $name = '';

    #[Assert\NotBlank(message: "Email address is required")]
    #[Assert\Email(message: "Invalid email address")]
    public string $email = '';

    #[Assert\NotBlank(message: "Subject is required")]
    #[Assert\Length(max: 120, maxMessage: "The subject cannot be longer than {{ limit }} characters.")]
    public string $subject = '';

    #[Assert\NotBlank(message: "Message is required")]
    #[Assert\Length(
        min: 10,
        max: 2000,
        minMessage: "Your message must be at least {{ limit }} characters long.",
        maxMessage: "Your message cannot exceed {{ limit }} characters."
    )]
    public string $message = '';
}

