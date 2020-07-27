<?php
namespace App\Entity;

use DateTime;
use App\Entity\Valideur;
use App\Helper\DateHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Valideurs
{
	protected $description;

	protected $valideurs;
	
    public function __construct()
    {
        $this->valideurs = new ArrayCollection();
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getValideurs()
    {
		return $this->valideurs;
	}
	
	public function addValideur(Valideur $valideur)
    {
        $this->valideurs->add($valideur);
    }

    public function removeValideur(Valideur $valideur)
    {
        $this->valideurs->removeElement($valideur);
	}

	/**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
		$endDate = isset($_POST['endDate']) ? DateTime::createFromFormat("d/m/Y H:i:s", $_POST['endDate']) : NULL;
		DateHelper::validateMyDates($this->valideurs, $context, $endDate);
	}
}