<?php
namespace App\Entity;

use App\Entity\Dsi;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\Common\Collections\ArrayCollection;

class Dsis
{
	protected $description;

	protected $dsis;
	
    public function __construct()
    {
        $this->dsis = new ArrayCollection();
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
	}
	
    public function getDsis()
    {
		return $this->dsis;
	}
	
	public function addDsi(Dsi $dsi)
    {
        $this->dsis->add($dsi);
    }

    public function removeDsi(Dsi $dsi)
    {
        $this->dsis->removeElement($dsi);
	}

	/**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
		$errorMessages = [];
		$length = count($this->dsis);
		
		for ($i = 0; $i < $length; $i++) {
			$dsi1 = $this->dsis[$i];

			if (!is_null($dsi1)) {
				for ($j = $i+1; $j < $length; $j++) {
					$dsi2 = $this->dsis[$j];
					
					if (!is_null($dsi2)) {
						if ($dsi1->getDateDeb() <= $dsi2->getDateFin() && $dsi2->getDateDeb() <= $dsi1->getDateFin()) {
							$errorMessages[] = 'Les périodes n°' . ($i + 1) . ' et n°' . ($j + 1) . ' se chevauchent. Veuillez les modifier.';
						}
					}
				}
	
				if ($dsi1->getDateDeb() > $dsi1->getDateFin()) {
					$errorMessages[] = 'La date de début (' . $dsi1->getDateDeb()->format('d/m/Y') . ') ne doit pas être antérieure à la date de fin (' . $dsi1->getDateFin()->format('d/m/Y') . ')';
				}
			}
		}

		foreach ($errorMessages as $message) {
			$context->buildViolation($message)
			->atPath('dsis')
			->addViolation();
		}
    }
}