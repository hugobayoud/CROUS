<?php
namespace App\Entity;

use App\Entity\ApplicationDemande;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\Common\Collections\ArrayCollection;

class ApplicationDemandes
{
	protected $description;

	protected $applicationDemandes;
	
    public function __construct()
    {
        $this->applicationDemandes = new ArrayCollection();
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
	}
	
    public function getApplicationDemandes()
    {
		return $this->applicationDemandes;
	}
	
	public function addApplicationDemande(ApplicationDemande $applicationDemande)
    {
        $this->applicationDemandes->add($applicationDemande);
    }

    public function removeApplicationDemande(ApplicationDemande $applicationDemande)
    {
        $this->applicationDemandes->removeElement($applicationDemande);
	}

	/**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
		$errorMessages = [];
		$length = count($this->applicationDemandes);
		
		for ($i = 0; $i < $length; $i++) {
			$applicationDemande1 = $this->applicationDemandes[$i];

			if (!is_null($applicationDemande1)) {
				for ($j = $i+1; $j < $length; $j++) {
					$applicationDemande2 = $this->applicationDemandes[$j];
					
					if (!is_null($applicationDemande2)) {
						if ($applicationDemande1->getApplication()->getId() === $applicationDemande2->getApplication()->getId()) {
							$errorMessages[] = 'L\'application ' . $applicationDemande1->getApplication()->getCode() . ' se retrouve au moins deux fois dans la demande. Veuillez ne garder qu\'une seule période par application.';
						}
					}
				}
	
				if ($applicationDemande1->getDateDeb() > $applicationDemande1->getDateFin()) {
					$errorMessages[] = $applicationDemande1->getApplication()->getCode() . ' : La date de début (' . $applicationDemande1->getDateDeb()->format('d/m/Y') . ') ne doit pas être antérieure à la date de fin (' . $applicationDemande1->getDateFin()->format('d/m/Y') . ')';
				}
			}
		}

		foreach ($errorMessages as $message) {
			$context->buildViolation($message)
			->atPath('applicationDemandes')
			->addViolation();
		}
	}
}