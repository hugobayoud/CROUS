<?php
namespace App\Helper;

use DateTime;
use Exception;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class DateHelper 
{
	public static function calculDateFin(\DateTime $dateDeb): \DateTime
	{
		$annee = $dateDeb->format("Y");

		if ((int)$dateDeb->format("m") >= 9) {
			$annee++;
		}

		return new \DateTime("31-08-$annee 23:59:59");
	}

	public static function formatMyDate(\DateTime $date = NULL): ?string
	{
		if ($date === NULL) {
			return NULL;
		}
		return $date->format("d/m/Y");
	}

	public static function fullyFormatMyDate(\DateTime $date): string
	{
		return $date->format("d/m/Y H:i:s");
	}

	/**
	 * Valide un tableau (array ouAarrayCollection) de DateTime et inscrit les erreurs pour affichage
	 * @param void
     */
	public static function validateMyDates($collection, ExecutionContextInterface $context, DateTime $endDate = NULL, bool $acceptOverlapping = FALSE): void
	{
		// Si $collection est ni un array, ni un ArrayCollection on renvoie une exception
		if (!$collection instanceof ArrayCollection && !is_array($collection)) {
			throw new Exception('DateHelper::validateMyDates() : l\'argument 1 n\'est pas un array ni un ArrayCollection');
		} else {
			$dates = [];
			// On réinitialise toutes les clés du tableau
			foreach ($collection as $datetimeObject) {
				$dates[] = $datetimeObject;
			}
		}

		// Nombre de date à vérifier
		$length = count($dates);
		// Tableau des messages d'erreur
		$errorMessages = [];
		// boolean
		$negative = false;

		for ($i = 0; $i < $length; $i++) {
			$date1 = $dates[$i];

			/* CHEVAUCHEMENT DES PERIODES ENTRE ELLES */
			if (!$acceptOverlapping) {
				for ($j = $i+1; $j < $length; $j++) {
					$date2 = $dates[$j];
					
					if ($date1->getDateDeb() <= $date2->getDateFin() && $date2->getDateDeb() <= $date1->getDateFin()) {
						$errorMessages[] = 'Les périodes n°' . ($i + 1) . ' et n°' . ($j + 1) . ' se chevauchent. Veuillez les modifier.';
					}
				}
			}

			/* DATE DE DEBUT SUPERIEURE A DATE DE FIN (PERIODE NEGATIVE) */
			if ($date1->getDateDeb() > $date1->getDateFin()) {
				$negative = true;
				$errorMessages[] = 'Période n°' . ($i +1) . ' : La date de début (' . $date1->getDateDeb()->format('d/m/Y') . ') ne doit pas être antérieure à la date de fin (' . $date1->getDateFin()->format('d/m/Y') . ')';
			}

			/* PERIODE PASSEE PAR RAPPORT A LA DATE DU JOUR */
			if (!$negative && ($date1->getDateFin()->format("Ymd") < (new \DateTime('now'))->format("Ymd"))) {
				$errorMessages[] = 'La période n°' . ($i + 1) . ' est totalement passée et les droits associés sont donc déjà supprimés. Veuillez modifier cette période.';
			}

			/* PERIODE APRES LA FIN DE CONTRAT DE L'AGENT */
			if (!is_null($endDate)) {
				if($date1->getDateDeb() > $endDate) {
			 		$errorMessages[] = 'La période n°' . ($i + 1) . ' est au delà de la période de contrat de l\'agent. Veuillez modifier en conséquence.';
			 	} else if ($date1->getDateFin() > $endDate) {
			 		$errorMessages[] = 'La période n°' . ($i + 1) . ' se termine après la date de fin de contrat de l\'agent. Veuillez modifier cette période.';
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