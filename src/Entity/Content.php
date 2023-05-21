<?php

namespace App\Entity;

use App\Repository\ContentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentRepository::class)]
class Content
{
    #[ORM\Id]
    #[ORM\GeneratedValue()]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 1000)]
    private string $description;

    #[ORM\Column]
    private int $minimumAge;

    #[ORM\ManyToOne(inversedBy: 'content')]
    #[ORM\JoinColumn(nullable: false)]
    private Channel $channel;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMinimumAge(): int
    {
        return $this->minimumAge;
    }

    public function setMinimumAge(int $minimumAge): self
    {
        $this->minimumAge = $minimumAge;

        return $this;
    }

    public function getChannel(): int
    {
        return $this->channel->getId();
    }

    public function setChannel(Channel $channel): self
    {
        $this->channel = $channel;

        return $this;
    }
}
