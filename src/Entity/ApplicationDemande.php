<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="application_demande")
 */
class ApplicationDemande
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
	private $id;
	
    /**
     * @ORM\ManyToOne(targetEntity="Application", inversedBy="demandes")
     * @ORM\JoinColumn(nullable=false)
     */
	private $application;
	
    /**
     * @ORM\ManyToOne(targetEntity="Demande", inversedBy="applications")
     * @ORM\JoinColumn(nullable=false)
     */
	private $demande;
	
    /**
     * @ORM\Column(type="boolean")
     */
	private $a_supprimer;

	/**
     * @ORM\Column(type="datetime")
     */
    private $date_deb;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_fin;
	
    public function getId()
    {
        return $this->id;
	}
	
    public function getApplication()
    {
        return $this->application;
	}
	
    public function setApplication($application)
    {
        $this->application = $application;
	}
	
    public function getDemande()
    {
        return $this->demande;
	}
	
    public function setDemande($demande)
    {
        $this->demande = $demande;
	}
	
    public function getASupprimer()
    {
        return $this->a_supprimer;
	}
	
    public function setASupprimer(bool $a_supprimer)
    {
        $this->a_supprimer = $a_supprimer;
	}

	public function getDateDeb(): ?\DateTimeInterface
    {
        return $this->date_deb;
    }

    public function setDateDeb(\DateTimeInterface $date_deb): self
    {
        $this->date_deb = $date_deb;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;

        return $this;
	}
	
	/**
	 * Retourne si l'application est une nouvelle application dont il faut ajouter les droits (pour un agent et un service donné)
	 * @return bool
	 */
	public function needNewAccess(): bool
	{
		// Pour chaque couple (user, service) pour une application
		foreach ($this->application->getCouples() as $droitEffectif) {
			if ($this->demande->getUser() === $droitEffectif->getCouple()->getUser() && $this->demande->getService() === $droitEffectif->getCouple()->getService()) {
				return false;
			}
		}
		return true;
	}
}