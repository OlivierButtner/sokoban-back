<?php

namespace App\Entity;

use App\Repository\BoardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BoardRepository::class)]
class Board
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['board_game'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['board_game'])]
    private ?int $row = null;

    #[ORM\Column]
    #[Groups(['board_game'])]
    private ?int $col = null;

    #[ORM\OneToMany(mappedBy: 'board_id', targetEntity: Row::class)]
    #[Groups(['board_game'])]
    private Collection $rows;

    public function __construct()
    {
        $this->rows = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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

    public function getCol(): ?int
    {
        return $this->col;
    }

    public function setCol(int $col): self
    {
        $this->col = $col;

        return $this;
    }

    /**
     * @return Collection<int, Row>
     */
    public function getRows(): Collection
    {
        return $this->rows;
    }

    public function addRow(Row $row): self
    {
        if (!$this->rows->contains($row)) {
            $this->rows->add($row);
            $row->setBoardId($this);
        }

        return $this;
    }

    public function removeRow(Row $row): self
    {
        if ($this->rows->removeElement($row)) {
            // set the owning side to null (unless already changed)
            if ($row->getBoardId() === $this) {
                $row->setBoardId(null);
            }
        }

        return $this;
    }
}
