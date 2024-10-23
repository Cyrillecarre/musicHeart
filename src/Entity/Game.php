<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $end_date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $result_date = null;

    #[ORM\ManyToOne(inversedBy: 'games')]
    private ?Admin $admin = null;

    /**
     * @var Collection<int, Participation>
     */
    #[ORM\OneToMany(targetEntity: Participation::class, mappedBy: 'game_id')]
    private Collection $participations;

    /**
     * @var Collection<int, Guess>
     */
    #[ORM\OneToMany(targetEntity: Guess::class, mappedBy: 'game_id')]
    private Collection $guesses;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
        $this->guesses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeInterface $end_date): static
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function getResultDate(): ?\DateTimeInterface
    {
        return $this->result_date;
    }

    public function setResultDate(\DateTimeInterface $result_date): static
    {
        $this->result_date = $result_date;

        return $this;
    }

    public function getAdmin(): ?Admin
    {
        return $this->admin;
    }

    public function setAdmin(?Admin $admin): static
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @return Collection<int, Participation>
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participation): static
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);
            $participation->setGameId($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getGameId() === $this) {
                $participation->setGameId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Guess>
     */
    public function getGuesses(): Collection
    {
        return $this->guesses;
    }

    public function addGuess(Guess $guess): static
    {
        if (!$this->guesses->contains($guess)) {
            $this->guesses->add($guess);
            $guess->setGameId($this);
        }
        return $this;
    }

    public function removeGuess(Guess $guess): static
    {
        if ($this->guesses->removeElement($guess)) {
            // set the owning side to null (unless already changed)
            if ($guess->getGameId() === $this) {
                $guess->setGameId(null);
            }
        }
        return $this;
    }
}
