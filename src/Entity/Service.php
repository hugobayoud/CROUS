<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
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
	 * @Assert\Length(
	 * 		min=1,
	 * 		minMessage="Le code d'un service doit faire entre 1 et 10 caractères",
	 * 		max=10,
	 * 		maxMessage="Le code d'un service doit faire entre 1 et 10 caractères"
	 * 	)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
	 * @Assert\Length(
	 * 		max=20,
	 * 		maxMessage="Le libellé (court) d'un service doit ne doit pas dépasser 20 caractères"
	 * 	)
     */
    private $libelle_court;

    /**
     * @ORM\Column(type="string", length=80, nullable=true)
	 * @Assert\Length(
	 * 		max=80,
	 * 		maxMessage="Le libellé (long) d'un service doit ne doit pas dépasser 80 caractères"
	 * 	)
     */
    private $libelle_long;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="services")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=Demande::class, mappedBy="service", orphanRemoval=true)
     */
	private $demandes;
	
	/**
     * @ORM\OneToMany(targetEntity=Valideur::class, mappedBy="user", orphanRemoval=true)
     */
    private $valideurs;
	
    public function __construct()
    {
		$this->users = new ArrayCollection();
		$this->valideurs = new ArrayCollection();
        $this->demandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return strtoupper($this->code);
    }

    public function setCode(string $code): self
    {
        $this->code = strtoupper($code);

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

    public function getLibelle_long(): ?string
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
	public function __toString()
	{
		return $this->code;
	}

    /**
     * @return Collection|Demande[]
     */
    public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(Demande $demande): self
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes[] = $demande;
            $demande->setService($this);
        }

        return $this;
    }

    public function removeDemande(Demande $demande): self
    {
        if ($this->demandes->contains($demande)) {
            $this->demandes->removeElement($demande);
            // set the owning side to null (unless already changed)
            if ($demande->getService() === $this) {
                $demande->setService(null);
            }
        }

        return $this;
	}

	/**
	 * Retourne le nombre d'agent liés à ce service
	 * @return int : nombre d'agent 
	*/
	public function getNumberOfAgentsLinked()
	{
		return count($this->users);
	}

	/**
	 * Retourne le nombre de demandes actuelles pour ce service
	 * @return int : nombre de demandes 
	*/
	public function getNumberOfDemandsLinked()
	{
		return count($this->demandes);
	}

	/**
     * @return Collection|Valideur[]
     */
    public function getValideurs(): Collection
    {
        return $this->valideurs;
    }

    public function addValideur(Valideur $valideur): self
    {
        if (!$this->valideurs->contains($valideur)) {
            $this->valideurs[] = $valideur;
            $valideur->setService($this);
        }

        return $this;
    }

    public function removeValideur(Valideur $valideur): self
    {
        if ($this->valideurs->contains($valideur)) {
            $this->valideurs->removeElement($valideur);
            // set the owning side to null (unless already changed)
            if ($valideur->getService() === $this) {
                $valideur->setService(null);
            }
        }

        return $this;
    }

}
