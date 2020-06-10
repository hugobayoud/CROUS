<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ServiceRepository::class)
 * @UniqueEntity(
 * 		fields={"code"},
 * 		message="Code déjà utilisé pour un autre service"
 * 	)
 */
class Service
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $libelle_court;

    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     */
    private $libelle_long;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="services")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=Validateur::class, mappedBy="id_service")
     */
    private $validateurs;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->validateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getLibelleCourt(): ?string
    {
        return $this->libelle_court;
	}
	
	public function getlibelle_court(): ?string
               	{
               		return $this->getLibelleCourt();
               	}

    public function setLibelleCourt(?string $libelle_court): self
    {
        $this->libelle_court = $libelle_court;

        return $this;
    }

    public function getLibelleLong(): ?string
    {
        return $this->libelle_long;
    }

    public function setLibelleLong(?string $libelle_long): self
    {
        $this->libelle_long = $libelle_long;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
	}
	
	/* AUTRES FONCTIONS */

    /**
     * @return Collection|Validateur[]
     */
    public function getValidateurs(): Collection
    {
        return $this->validateurs;
    }

    public function addValidateur(Validateur $validateur): self
    {
        if (!$this->validateurs->contains($validateur)) {
            $this->validateurs[] = $validateur;
            $validateur->setIdService($this);
        }

        return $this;
    }

    public function removeValidateur(Validateur $validateur): self
    {
        if ($this->validateurs->contains($validateur)) {
            $this->validateurs->removeElement($validateur);
            // set the owning side to null (unless already changed)
            if ($validateur->getIdService() === $this) {
                $validateur->setIdService(null);
            }
        }

        return $this;
    }
}
