<?php
namespace App\Entity;

use App\Entity\Couple;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ServiceRepository")
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
     * @ORM\OneToMany(targetEntity=Couple::class, mappedBy="service", orphanRemoval=true)
     */
	private $couples;
	
	/**
     * @ORM\OneToMany(targetEntity=Valideur::class, mappedBy="service", orphanRemoval=true)
     */
	private $valideurs;
	
    public function __construct()
    {
		$this->users = new ArrayCollection();
		$this->valideurs = new ArrayCollection();
		$this->demandes = new ArrayCollection();
		$this->couples = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return mb_strtoupper($this->code);
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

    public function getLibelle_long(): ?string
    {
        return $this->libelle_long;
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
	
	    /**
     * @return Collection|User[]
     */
    public function getValidatedUsers(): Collection
    {
		$allValidated = new ArrayCollection();

		foreach ($this->users as $user) {
			if (is_null($user->getActivationToken())) {
				$allValidated->add($user);
			}
		}

        return $allValidated;
	}

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
			if (is_null($user->getActivationToken())) {
				$this->users[] = $user;
			}
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
	
	/**
	 * Récupère les demandes lié à un service qui sont à l'état 0 (attente de validation d'un valideur)
	 * 
	 * @return Demande[]|NULL
	 */
	public function getToApproveDemands()
	{
		$toApprove = [];
		foreach ($this->demandes as $demande) {
			if ($demande->getEtat() === 0) {
				$toApprove[] = $demande;
			}
		}

		return $toApprove;
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
     * @return Collection|Couple[]
     */
    public function getCouples(): Collection
    {
        return $this->couples;
	}
	
    public function addCouple(Couple $couple): self
    {
        if (!$this->couples->contains($couple)) {
            $this->couples[] = $couple;
            $couple->setService($this);
        }

        return $this;
    }

    public function removeCouple(Couple $couple): self
    {
        if ($this->couples->contains($couple)) {
            $this->couples->removeElement($couple);

			if ($couple->getService() === $this) {
                $couple->setService(null);
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
	
	/**
	 * Nombre actuel de valideur dans ce service
	 */
	public function getNumberOfCurrentValidator()
	{
		$count = 0;
		foreach ($this->users as $user) {
			if ($user->isValidator($this->id)) {
				$count++;
			}
		}
		
		return $count;
	}

	/**
	 * Renvoi l'état de la demande associé au service pour un agent
	 * @return int :
	 * 		-1 	: demande pas encore faite (rien n'existe dans base pour ce service pour ce user)
	 * 		0 	: demande faite mais pas validée par un valideur du service
	 * 		1 	: demande validée par un valideur du service mais pas encore traitée par la DSI
	 * 		2 	: demande traitée par la DSI
	 * Si etat = 2 ,Tant que l'user ne souhaite pas refaire une nouvelle demande pour ce service, la demande reste à 2 puis revint à 0 quand il clique sur "nouvelle demande"  
	 */
	public function getStateDemand(int $userId): int
	{
		foreach ($this->demandes as $demande) {
			if ($demande->getUser()->getId() === $userId) {
				return $demande->getEtat();
			}
		}
		
		return -1;
	}

	/**
	 * Retourne la date de dernière modification d'une demande pour un service et un agent donné
	 * @return string|NULL
	 */
	public function getLastModif(int $userId): ?string
	{
		foreach ($this->demandes as $demande) {
			if ($demande->getUser()->getId() === $userId) {
				return $demande->getCreatedAt()->format('d/m/Y');
			}
		}

		return NULL;
	}

}
