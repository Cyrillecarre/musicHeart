<?php

namespace App\Entity;

use App\Repository\GuessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuessRepository::class)]
class Guess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'guesses')]
    private ?Game $game_id = null;

    #[ORM\ManyToOne(inversedBy: 'guesses')]
    private ?Participation $music_url_id = null;

    #[ORM\ManyToOne(inversedBy: 'guesses')]
    private ?Participation $guessed_participant_id = null;

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

    public function getMusicUrlId(): ?Participation
    {
        return $this->music_url_id;
    }

    public function setMusicUrlId(?Participation $music_url_id): static
    {
        $this->music_url_id = $music_url_id;

        return $this;
    }

    public function getGuessedParticipantId(): ?Participation
    {
        return $this->guessed_participant_id;
    }

    public function setGuessedParticipantId(?Participation $guessed_participant_id): static
    {
        $this->guessed_participant_id = $guessed_participant_id;

        return $this;
    }
}
