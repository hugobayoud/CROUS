<?php

namespace App\Entity;

use App\Entity\Service;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ValideurRepository;

/**
 * @ORM\Entity(repositoryClass=ValideurRepository::class)
 */
class Valideur
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="valideurs")
     * @ORM\JoinColumn(nullable=false)
     */
	private $user;
	
	/**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="valideurs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $service;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_deb;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_fin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
	}
	
	public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->serviceer = $service;

        return $this;
    }

    public function getDateDeb(): ?\DateTimeInterface
    {
        return $this->date_deb;
    }

    public function setDateDeb(\DateTimeInterface $date_deb): self
    {
        $this->date_deb = $date_deb;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;

        return $this;
    }
}
