<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\HttpFoundation\Session\Session;

class OverlapPeriodsValidator extends ConstraintValidator
{
    public function validate($dsis, /*$value,*/ Constraint $constraint)
    {
        // if (!$constraint instanceof OverlapPeriods) {
        //     throw new UnexpectedTypeException($constraint, OverlapPeriods::class);
        // }

        // // custom constraints should ignore null and empty values to allow
        // // other constraints (NotBlank, NotNull, etc.) take care of that
        // if (null === $value || '' === $value) {
        //     return;
        // }

        // if (!$value instanceof ArrayCollection) {
        //     // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
        //     throw new UnexpectedValueException($value, 'ArrayCollection');

        //     // separate multiple types using pipes
        //     // throw new UnexpectedValueException($value, 'string|int');
        // }

		// foreach ($value as $dsi1) {
		// 	foreach ($value as $dsi2) {
		// 		if (($dsi1->getDateDeb()->getTimestamp() <= $dsi2->getDateFin()->getTimestamp()) && ($dsi2->getDateDeb()->getTimestamp() <= $dsi1->getDateFin()->getTimestamp())) {
		// 			$this->context->buildViolation($constraint->message)
		// 			->setParameter('{{ ArrayCollection }}', $value)
		// 			->addViolation();
		// 		}
		// 	}
		// }

		$overlapError = false;
		$negativPeriodError = false;

		if (count($dsis->getDsis()) > 1) {
			foreach ($dsis->getDsis() as $dsi1) {
				foreach ($dsis->getDsis() as $dsi2) {
					if ($dsi1->getId() != $dsi2->getId()) {
						if (($dsi1->getDateDeb()->getTimestamp() <= $dsi2->getDateFin()->getTimestamp()) && ($dsi2->getDateDeb()->getTimestamp() <= $dsi1->getDateFin()->getTimestamp())) {
							$session = new Session();
							if (!$overlapError) {
								$session->getFlashBag()->add('warning','Au moins deux périodes se chevauchent, veuillez vérifier les dates');
								$overlapError = true;
							}
							$this->context->buildViolation($constraint->message)
							->atPath('dsis')
							->addViolation();
						}
					}
			   }

			   if ($dsi1->getDateDeb()->getTimestamp() > $dsi1->getDateFin()->getTimestamp()) {
					if (!$negativPeriodError) {
						$session = new Session();
						$session->getFlashBag()->add('warning','Au moins une date de fin est antérieure à la date de début, veuillez vérifier les dates');
						$negativPeriodError = true;
					}
					$this->context->buildViolation($constraint->message)
					->atPath('dsis')
					->addViolation();
			   }
		   }
		}
    }
}