<?php
namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="droit_effectif")
 */
class DroitEffectif
{
	const DAYS_BEFORE = 20;

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
	
	/**
	 * Vérifie si le droit effectif doit être supprimé de la Base
	 * @return bool
	 */
	public function hasToBeDeleted(): bool
	{
		return $this->status === 's';
	}

	/**
	 * Vérifie si le droit effectif voit son échéance repoussée.
	 * @return bool
	 */
	public function hasToBePostponed(): bool
	{
		return ($this->status === 'c' && !is_null($this->nouvelle_echeance));
	}

	/**
	 * Si le droit reste inchangés, ni supprimé, ni date repoussée
	 * @return bool
	 */
	public function hasToRemain(): bool
	{
		return (!$this->hasToBeDeleted() && !$this->hasToBePostponed());
	}

	/**
	 * Retourne si ce droit effectif arrive bientôt à terme. Lit un fichier conf qui donne le nombre de jours
	 * @return bool
	 */
	public function finishedSoon(): bool
	{
		$now = new DateTime('now');
		// On calcule le nombre de jour de différence (on ne prend pas la valeur absolue. Si la date est passée, on doit le notifier)
		$days = intval(date_diff($now, $this->date_fin)->format('%r%a'));
		if ($days < self::DAYS_BEFORE) {
			return true;
		}

		return false;
	}
}