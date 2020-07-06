<?php

namespace App\Entity;

use App\Entity\Couple;
use App\Entity\DroitEffectif;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ApplicationRepository::class)
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
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
	 * @Assert\Length(
	 * 		max=50,
	 * 		maxMessage="Le libellé d'une application doit ne doit pas dépasser 50 caractères"
	 * 	)
     */
	private $libelle;
	
	/**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
	private $type;
	
	/**
     * @ORM\OneToMany(targetEntity="ApplicationDemande", mappedBy="application", fetch="EXTRA_LAZY")
     */
	private $demandes;

	/**
     * @ORM\OneToMany(targetEntity="DroitEffectif", mappedBy="application", fetch="EXTRA_LAZY")
     */
	private $couples;
	
	public function __construct()
    {
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

    public function getLibelle(): ?string
    {
        return ucwords(mb_strtolower($this->libelle));
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
	}
	
	/* AUTRES FONCTIONS */
	public function __toString()
	{
		return $this->code;
	}

    /**
     * Get the value of type
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */ 
    public function setType($type): self
    {
        $this->type = $type;

        return $this;
	}

	public function addDemande(Demande $demande)
    {
        if ($this->demandes->contains($demande)) {
            return;
        }
        $this->demandes[] = $demande;
        // not needed for persistence, just keeping both sides in sync
        $demande->addApplication($this);
    }
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
     * @return ArrayCollection|ApplicationDemande[]
     */
    public function getDemandes()
    {
        return $this->demandes;
	}
	
	// Couples
	public function addCouple(Couple $couple)
    {
        if ($this->couples->contains($couple)) {
            return;
        }
        $this->couples[] = $couple;
        // not needed for persistence, just keeping both sides in sync
        $couple->addApplication($this);
    }
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
		foreach ($this->demandes as $appli_demande) {
			if ($appli_demande->getDemande()->getUser()->getId() === $userId && $appli_demande->getDemande()->getService()->getId() === $serviceId) {
				return true;
			}
		}

		return false;
	}
}
