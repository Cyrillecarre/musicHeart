<?php
namespace App\Entity;

use App\Repository\ParticipationRepository;
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

    #[ORM\ManyToOne(targetEntity: Participant::class, inversedBy: 'participations')]
    private ?Participant $participant = null;

    #[ORM\Column(length: 255)]
    private ?string $music_url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $support_text = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_correct = null;

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

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): static
    {
        $this->participant = $participant;

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
}
