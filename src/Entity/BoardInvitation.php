<?php

namespace App\Entity;

use App\Repository\BoardInvitationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BoardInvitationRepository::class)]
#[ORM\Index(columns: ['token'], name: 'idx_token')]
#[ORM\Index(columns: ['email'], name: 'idx_email')]
class BoardInvitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Board::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Board $board = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $token = null;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $invitedBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column]
    private bool $isAccepted = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $acceptedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->expiresAt = new \DateTimeImmutable('+7 days');
        $this->token = bin2hex(random_bytes(32));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBoard(): ?Board
    {
        return $this->board;
    }

    public function setBoard(?Board $board): static
    {
        $this->board = $board;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower(trim($email));
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function getInvitedBy(): ?Account
    {
        return $this->invitedBy;
    }

    public function setInvitedBy(?Account $invitedBy): static
    {
        $this->invitedBy = $invitedBy;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTimeImmutable();
    }

    public function isAccepted(): bool
    {
        return $this->isAccepted;
    }

    public function setIsAccepted(bool $isAccepted): static
    {
        $this->isAccepted = $isAccepted;
        if ($isAccepted && !$this->acceptedAt) {
            $this->acceptedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isAccepted;
    }
}
