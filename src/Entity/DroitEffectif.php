<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="droit_effectif")
 */
class DroitEffectif
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
	private $id;

	    /**
     * @ORM\ManyToOne(targetEntity="Application", inversedBy="couples")
     * @ORM\JoinColumn(nullable=false)
     */
	private $application;
	
    /**
     * @ORM\ManyToOne(targetEntity="Couple", inversedBy="applications")
     * @ORM\JoinColumn(nullable=false)
     */
	private $couple;

	/**
     * @ORM\Column(type="datetime")
     */
    private $date_deb;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_fin;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $nouvelle_echeance;
	
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
	
    public function getCouple()
    {
        return $this->couple;
	}
	
    public function setCouple($couple)
    {
        $this->couple = $couple;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getNouvelleEcheance(): ?\DateTimeInterface
    {
        return $this->nouvelle_echeance;
    }

    public function setNouvelleEcheance(?\DateTimeInterface $nouvelle_echeance): self
    {
        $this->nouvelle_echeance = $nouvelle_echeance;

        return $this;
    }
}