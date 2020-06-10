<?php
namespace App\Helper;

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
}