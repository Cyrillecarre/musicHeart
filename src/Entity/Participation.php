<?php

namespace App\Entity;

use App\Repository\ParticipationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'participations')]
    private ?Game $game_id = null;

    /**
     * @var Collection<int, Participant>
     */
    #[ORM\OneToMany(targetEntity: Participant::class, mappedBy: 'participation')]
    private Collection $participant_id;

    #[ORM\Column(length: 255)]
    private ?string $music_url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $support_text = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_correct = null;

    /**
     * @var Collection<int, Guess>
     */
    #[ORM\OneToMany(targetEntity: Guess::class, mappedBy: 'music_url_id')]
    private Collection $guesses;

    public function __construct()
    {
        $this->participant_id = new ArrayCollection();
        $this->guesses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGameId(): ?Game
    {
        return $this->game_id;
    }

    public function setGameId(?Game $game_id): static
    {
        $this->game_id = $game_id;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipantId(): Collection
    {
        return $this->participant_id;
    }

    public function addParticipantId(Participant $participantId): static
    {
        if (!$this->participant_id->contains($participantId)) {
            $this->participant_id->add($participantId);
            $participantId->setParticipation($this);
        }

        return $this;
    }

    public function removeParticipantId(Participant $participantId): static
    {
        if ($this->participant_id->removeElement($participantId)) {
            // set the owning side to null (unless already changed)
            if ($participantId->getParticipation() === $this) {
                $participantId->setParticipation(null);
            }
        }

        return $this;
    }

    public function getMusicUrl(): ?string
    {
        return $this->music_url;
    }

    public function setMusicUrl(string $music_url): static
    {
        $this->music_url = $music_url;

        return $this;
    }

    public function getSupportText(): ?string
    {
        return $this->support_text;
    }

    public function setSupportText(?string $support_text): static
    {
        $this->support_text = $support_text;

        return $this;
    }

    public function isCorrect(): ?bool
    {
        return $this->is_correct;
    }

    public function setCorrect(?bool $is_correct): static
    {
        $this->is_correct = $is_correct;

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
            $guess->setMusicUrlId($this);
        }

        return $this;
    }

    public function removeGuess(Guess $guess): static
    {
        if ($this->guesses->removeElement($guess)) {
            // set the owning side to null (unless already changed)
            if ($guess->getMusicUrlId() === $this) {
                $guess->setMusicUrlId(null);
            }
        }

        return $this;
    }
}
