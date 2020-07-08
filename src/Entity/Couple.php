<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CoupleRepository")
 * @UniqueEntity(
 * 		fields={"user_id", "service_id"},
 * 		message="Il existe deja une ligne pour cet agent pour ce service en BDD"
 * 	)
 */
class Couple
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="couples")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="couples")
     * @ORM\JoinColumn(nullable=false)
     */
    private $service;

    /**
     * @ORM\OneToMany(targetEntity="DroitEffectif", mappedBy="couple")
     */
	private $applications;
	
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

	/**
     * @return ArrayCollection|DroitEffectif[]
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
        $application->addCouple($this);
    }
    public function removeApplication(Application $application)
    {
        if (!$this->applications->contains($application)) {
            return;
        }
		$this->applications->removeElement($application);
        // not needed for persistence, just keeping both sides in sync
        $application->removeCouple($this);
	}
	
	public function __toString()
	{
		return $this->user->getNom();
	}
}