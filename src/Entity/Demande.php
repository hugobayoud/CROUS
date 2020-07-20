<?php
namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\Column(type="datetime")
     */
	private $created_at;
	
    /**
     * @ORM\OneToMany(targetEntity="ApplicationDemande", mappedBy="demande")
     */
	private $applications;

    /**
     * @ORM\Column(type="string", length=14, nullable=true)
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $repertoires_serveur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mail_de;
	
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
		return (int)$this->created_at->diff(new DateTime('now'))->format('d'); 
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
}
