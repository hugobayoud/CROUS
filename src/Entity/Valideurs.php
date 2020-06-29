<?php
namespace App\Entity;

use App\Entity\Valideur;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\Common\Collections\ArrayCollection;

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
		$errorMessages = [];
		$length = count($this->valideurs);

		for ($i = 0; $i < $length; $i++) {
			$valideur1 = $this->valideurs[$i];

			if (!is_null($valideur1)) {
				for ($j = $i+1; $j < $length; $j++) {
					$valideur2 = $this->valideurs[$j];

					if (!is_null($valideur2)) {
						if ($valideur1->getDateDeb() <= $valideur2->getDateFin() && $valideur2->getDateDeb() <= $valideur1->getDateFin()) {
							$errorMessages[] = 'Les périodes n°' . ($i + 1) . ' et n°' . ($j + 1) . ' se chevauchent. Veuillez les modifier.';
						}
					}
				}

				if ($valideur1->getDateDeb() > $valideur1->getDateFin()) {
					$errorMessages[] = 'La date de début (' . $valideur1->getDateDeb()->format('d/m/Y') . ') ne doit pas être antérieure à la date de fin (' . $valideur1->getDateFin()->format('d/m/Y') . ')';
				}
			}
		}

		foreach ($errorMessages as $message) {
			$context->buildViolation($message)
			->atPath('valideurs')
			->addViolation();
		}
    }
}