<?php
namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass=DemandeRepository::class)
 */
class Demande
{
    /**
	 * @Id
     * @GeneratedValue()
     * @Column(type="integer", unique=true)
     */
    private $id;

    /**
	 * @ManyToOne(targetEntity="User", inversedBy="demandes")
     * @JoinColumn(nullable=false)
     */
	private $user;

    /**
	 * @ManyToOne(targetEntity="Service", inversedBy="demandes")
     * @JoinColumn(nullable=false)
     */
    private $service;

    /**
     * @Column(type="integer")
     */
	private $etat;
	
    /**
     * @Column(type="datetime")
     */
	private $created_at;
	
    /**
     * @OneToMany(targetEntity="ApplicationDemande", mappedBy="demande", cascade={"remove"})
     */
	private $applications;

    /**
     * @Column(type="string", length=14, nullable=true)
     */
    private $telephone;

    /**
     * @Column(type="string", length=255, nullable=true)
     */
    private $repertoires_serveur;

    /**
     * @Column(type="string", length=255, nullable=true)
     */
    private $mail_de;

    /**
     * @Column(type="boolean", options={"default":"0"})
     */
    private $prioritaire;
	
	public function __construct()
	{
		$this->applications = new ArrayCollection();
	}

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
	}
	
	/**
     * @return ArrayCollection|ApplicationDemande[]
     */
    public function getApplications()
    {
        return $this->applications;
	}
	
    public function addApplication(Application $application)
    {
        if ($this->applications->contains($application)) {
            return;
        }
		$this->applications[] = $application;
        // not needed for persistence, just keeping both sides in sync
        $application->addDemande($this);
	}
	
    public function removeApplication(Application $application)
    {
        if (!$this->applications->contains($application)) {
            return;
        }
		$this->applications->removeElement($application);
        // not needed for persistence, just keeping both sides in sync
        $application->removeDemande($this);
	}

    public function getTelephone(): ?string
    {
        return htmlentities($this->telephone);
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getRepertoiresServeur(): ?string
    {
        return $this->repertoires_serveur;
    }

    public function setRepertoiresServeur(?string $repertoires_serveur): self
    {
        $this->repertoires_serveur = $repertoires_serveur;

        return $this;
	}
		
	/**
	 * Retourne le nombre de jour depuis quand la demande a été créée
	 * @return int
	 */
	public function createdDaysAgo(): int
	{
		return (int)$this->created_at->diff(new DateTime('now'))->format('%a'); 
	}

    public function getMailDe(): ?string
    {
        return $this->mail_de;
    }

    public function setMailDe(?string $mail_de): self
    {
        $this->mail_de = $mail_de;

        return $this;
    }

    public function getPrioritaire(): bool
    {
        return $this->prioritaire;
	}
	
	public function isPrioritaire(): bool
    {
        return $this->prioritaire;
    }

    public function setPrioritaire(bool $prioritaire): self
    {
        $this->prioritaire = $prioritaire;

        return $this;
	}
}
