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
}