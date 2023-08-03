<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use App\Service\Time\TimeFormatter;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $date = null;

    #[ORM\Column(nullable: true)]
    private ?int $created_by = null;

    #[ORM\Column]
    private ?int $created_for = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $created_at = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_path = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->created_by;
    }

    public function setCreatedBy(?int $created_by=1): static
    {
        $this->created_by = $created_by;

        return $this;
    }

    public function getCreatedFor(): ?int
    {
        return $this->created_for;
    }

    public function setCreatedFor(int $created_for): static
    {
        $this->created_for = $created_for;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(): static
    {
        $this->created_at = TimeFormatter::formatTime();

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->image_path;
    }

    public function setImagePath(?string $image_path): static
    {
        $this->image_path = $image_path;

        return $this;
    }
}
