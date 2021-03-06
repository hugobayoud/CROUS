<?php
namespace App\Entity;

use App\Entity\Couple;
use App\Entity\DroitEffectif;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ApplicationRepository")
 * @UniqueEntity(
 * 		fields={"code"},
 * 		message="Code déjà utilisé pour une autre application"
 * 	)
 */
class Application
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
	 * 		minMessage="Le code d'un application doit faire entre 1 et 10 caractères",
	 * 		max=10,
	 * 		maxMessage="Le code d'une application doit faire entre 1 et 10 caractères"
	 * 	)
	 * CODE de l'application (est unique)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
	 * @Assert\Length(
	 * 		max=20,
	 * 		maxMessage="Le libellé (court) d'une application doit ne doit pas dépasser 20 caractères"
	 * 	)
	 * Libelle court de l'application (maximum 20 caractères)
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=80, nullable=true)
	 * @Assert\Length(
	 * 		max=80,
	 * 		maxMessage="Le libellé (long) d'une application doit ne doit pas dépasser 80 caractères"
	 * 	)
	 * Libelle long de l'application (maximum 80 caractères)
     */
    private $libelle_long;
	
	/**
     * @ORM\Column(type="string", length=2, nullable=true)
	 * Type de l'application :
	 * 		- t : transverse, l'appli est utile pour tous les services dont fait parti l'agent
	 * 		- p : profil, l'appli n'est utile que pour le service pour lequel elle a été demmandée
     */
	private $type;
	
	/**
     * @ORM\OneToMany(targetEntity="ApplicationDemande", mappedBy="application", fetch="EXTRA_LAZY")
	 * Toutes les demandes en cours pour lesquelles l'application est demandée
     */
	private $demandes;

	/**
     * @ORM\OneToMany(targetEntity="DroitEffectif", mappedBy="application", fetch="EXTRA_LAZY")
	 * Ensemble des couples (user, service) pour lesquels les droits sont ouverts
     */
	private $couples;
	
	public function __construct()
    {
        $this->demandes = new ArrayCollection();
        $this->couples = new ArrayCollection();
    }

	/* GETTERS & SETTERS */
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

    public function getLibelle(): ?string
    {
        return $this->libelle;
	}

	public function getLibelleCourt(): ?string
	{
		return $this->libelle;
	}

	public function setLibelle(?string $libelle_court): self
    {
        $this->libelle = $libelle_court;
        return $this;
	}

	public function setLibelleCourt(?string $libelle_court): self
    {
        $this->libelle = $libelle_court;
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

    public function getType()
    {
        return $this->type;
    }

    public function setType($type): self
    {
        $this->type = $type;

        return $this;
	}

	/**
     * @return ArrayCollection|ApplicationDemande[]
     */
    public function getDemandes()
    {
        return $this->demandes;
	}

	/**
	 * @return void
	 */
	public function addDemande(Demande $demande)
    {
        if ($this->demandes->contains($demande)) {
            return;
        }
        $this->demandes[] = $demande;
        // not needed for persistence, just keeping both sides in sync
        $demande->addApplication($this);
	}
	
	/**
	 * @return void
	 */
    public function removeDemande(Demande $demande)
    {
        if (!$this->demandes->contains($demande)) {
            return;
        }
        $this->demandes->removeElement($demande);
        // not needed for persistence, just keeping both sides in sync
        $demande->removeApplication($this);
	}

	/**
	 * @return void
	 */
	public function addCouple(Couple $couple)
    {
        if ($this->couples->contains($couple)) {
            return;
        }
        $this->couples[] = $couple;
        // not needed for persistence, just keeping both sides in sync
        $couple->addApplication($this);
	}
	
	/**
	 * @return void
	 */
    public function removeCouple(Couple $couple)
    {
        if (!$this->couples->contains($couple)) {
            return;
        }
        $this->couples->removeElement($couple);
        // not needed for persistence, just keeping both sides in sync
        $couple->removeApplication($this);
	}
	
    /**
     * @return ArrayCollection|DroitEffectif[]
     */
    public function getCouples()
    {
        return $this->couples;
	}
	
		
	/* AUTRES FONCTIONS */
	public function __toString()
	{
		return $this->code;
	}
	
	/**
	 * Retourne si l'application est transverse ou non. Par defaut, l'application n'est pas tranverse
	 * @return bool
	 */
	public function isTransverse(): bool
	{
		return $this->type === 't'; 
	}

	/**
	 * Retourne si une application est déjà demandée par un agent, pour un service précis
	 * @return bool
	 */
	public function isAlreadyRequested(int $userId, int $serviceId): bool
	{
		// Applications déjà en cours de demande
		foreach ($this->demandes as $appliDemande) {
			$demande = $appliDemande->getDemande();
			if ($demande->getUser()->getId() === $userId && $demande->getService()->getId() === $serviceId) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retourne si un couple (user, service) possède déjà les droits pour cette application
	 * @return bool
	 */
	public function hasAlreadyAccess(int $userId, int $serviceId): bool
	{
		// Applications déjà comme droit effectif pour l'agent pour ce service
		foreach ($this->couples as $droitEffectif) {
			// On vérifie que la date de fin du droit effectif n'est pas dépassée
			if ($droitEffectif->getDateFin() > (new DateTime('now'))) {
				$couple = $droitEffectif->getCouple();
				if ($couple->getUser()->getId() === $userId && $couple->getService()->getId() === $serviceId) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Compte le nombre de demandes en cours (état 0 ou 1) où cette application apparait
	 * 
	 * @return int
	 */
	public function countInDemands(): int
	{
		return count($this->demandes);
	}

	/**
	 * Compte le nombre de droits effectifs en cours ou a venir où cette application apparait
	 * 
	 * @return int
	 */
	public function countInRights(): int
	{
		$count = 0;
		$now = new DateTime('now');

		foreach ($this->couples as $droit_effectif) {
			if ($droit_effectif->getDateFin() > $now) {
				$count++;
			}
		}

		return $count;
	}
}
