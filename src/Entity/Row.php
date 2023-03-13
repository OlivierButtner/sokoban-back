<?php

namespace App\Entity;

use App\Repository\RowRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RowRepository::class)]
class Row
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['board_game'])]
    private ?int $row = null;

    #[ORM\Column(length: 255)]
    #[Groups(['board_game'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'rows')]
    private ?Board $board_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRow(): ?int
    {
        return $this->row;
    }

    public function setRow(int $row): self
    {
        $this->row = $row;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getBoardId(): ?Board
    {
        return $this->board_id;
    }

    public function setBoardId(?Board $board_id): self
    {
        $this->board_id = $board_id;

        return $this;
    }
}
