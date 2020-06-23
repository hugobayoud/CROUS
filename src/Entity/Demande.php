<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DemandeRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=DemandeRepository::class)
 * @UniqueEntity(
 * 		fields={"user_id", "service_id"},
 * 		message="Il existe deja une demande pour cet agent pour ce service en BDD"
 * 	)
 */
class Demande
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="demandes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="demandes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $service;

    /**
     * @ORM\Column(type="integer")
     */
    private $etat;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
	private $applications = [];
	
	/**
     * @ORM\Column(type="json", nullable=true)
     */
    private $new_applications = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

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
        $this->service = $service;

        return $this;
    }

    public function getEtat(): ?int
    {
        return $this->etat;
    }

    public function setEtat(int $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getApplications(): ?array
    {
        return $this->applications;
    }

    public function setApplications(?array $applications): self
    {
        $this->applications = $applications;

        return $this;
	}
	
	public function getNewApplications(): ?array
    {
        return $this->new_applications;
    }

    public function setNewApplications(?array $new_applications): self
    {
        $this->new_applications = $new_applications;

        return $this;
	}
	
	public function addnewApplication(?array $new_application): self
	{
		$this->new_applications[key($new_application)] = $new_application[key($new_application)];

		return $this;
	}

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}
