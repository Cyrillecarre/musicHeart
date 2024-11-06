<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Admin;

#[ORM\Entity]
class AccessToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $token;

    #[ORM\ManyToOne(targetEntity: Admin::class)]
    private Admin $admin;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $expirationDate;

    // Getters et Setters

    public function getId(): int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getAdmin(): Admin
    {
        return $this->admin;
    }

    public function setAdmin(Admin $admin): self
    {
        $this->admin = $admin;
        return $this;
    }

    public function getExpirationDate(): \DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(\DateTimeInterface $expirationDate): self
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }
}
