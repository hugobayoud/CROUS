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
	 * @ORM\OrderBy({"nom" = "ASC", "prenom" = "ASC", "email" = "ASC"})
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="Demande", mappedBy="service")
     */
	private $demandes;

	/**
     * @ORM\OneToMany(targetEntity=Couple::class, mappedBy="service")
     */
	private $couples;
	
	/**
     * @ORM\OneToMany(targetEntity=Valideur::class, mappedBy="service")
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
    public function getUsers(User $notWantedUser = NULL): Collection
    {
		return $this->users;
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

	/**
	 * Retourne tous les users du service dont le compte a été validé. Si un user est passé en paramètre, il est supprimé de ce tableau car non voulu
     * @return User[]
     */
    public function getValidatedUsers(User $notWantedUser = NULL)
    {
		$users = [];
		// Si user passé en paramètre, on enlève ce user de la liste
		if (!is_null($notWantedUser)) {
			if ($this->users->contains($notWantedUser)) {
				$this->users->removeElement($notWantedUser);
			}
		}

		// On ne garde que les utilisateurs dont le compte à été validé
		foreach ($this->users as $user) {
			if (is_null($user->getActivationToken())) {
				$users[] = $user;
			}
		}

        return $users;
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
	 * Récupère les demandes liées à un service qui sont à l'état 0 (attente de validation d'un valideur) ou 1 (que le valideur peut encore modifier)
	 * 
	 * @return Demande[]|NULL
	 */
	public function getToApproveDemands()
	{
		$toApprove = [];
		foreach ($this->demandes as $demande) {
			if ($demande->getEtat() === 0 || $demande->getEtat() === 1) {
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

	/**
	 * Retourne le nombre de demandes d'un service à l'état 0
	 * @return int
	 */
	public function countDemands0(): int
	{
		$count = 0;
		foreach ($this->demandes as $demande) {
			if ($demande->getEtat() === 0) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Retourne le nombre de droits-effectifs qui arrivent à terme bientôt dans l'ensemble d'un service
	 * @return int
	 */
	public function countFinishedSoon(): int
	{
		$count = 0;
		foreach ($this->couples as $couple) {
			$count += $couple->countRightsFinishedSoon();
		}

		return $count;
	}

	/**
	 * Compte le nombre d'agent a l'intérieur du service
	 * @return int
	 */
	public function countAgentsIn(): int
	{
		return count($this->users);
	}
}
