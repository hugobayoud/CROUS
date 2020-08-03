<?php
namespace App\Helper;

use DateTime;
use App\Entity\User;
use App\Helper\DateHelper;

class HtmlHelper {
	/**
	 * Retourne les informations d'un agent en HTML
	 * @return string
	 */
	public static function userInfo(User $user): string
	{
		$myHTML = '<div class="user-info-box">';
		$myHTML .= 		'<div class="flex-space-between">';
		$myHTML .= 			'<h2>' . $user->getNom() . ' ' . $user->getPrenom() . '</h2>';
		$myHTML .= 			'<div>' . $user->getEmail() . '</div>';
		$myHTML .= 		'</div>';
		$myHTML .= 		'<div>';
		$myHTML .= 			'<div>Contrat terminé le : <strong>' . DateHelper::formatMyDate($user->getDateFinValid()) . '</strong></div>';


		$listServices = self::listServices($user);
		if ($listServices === '') {
			$myHTML .= 			'<div>N\'est membre d\'aucun service</div>';
		} else {
			$myHTML .= 			'<div>Membre des services : ' . $listServices . '</div>';
		}

		$myHTML .= self::dsi($user);
		return $myHTML . '</div></div>';
	}

	/**
	 * Retourne si un agent est dsi ou non en HTML
	 * @return string
	 */
	public static function dsi(User $user): string
	{
		if (!$user->isDSI()) {
			$myHTML = '	<div>Ne possède pas la fonction DSI</div>';
		} else {
			$myHTML = '<div>Occupe la fonction DSI (jusqu\'au <strong> ' . $user->getDateFinDSI() . '</strong>)</div>';
		}

		return $myHTML;
	}

	/**
	 * Lister tous les services dont l'user est valideur en HTML
	 * @return string
	 */
	public static function servicesWhereValidator(User $user): ?string
	{
		// HTML de chaque service dont il est valideur actuellement
		$sv = [];
		$currentDate = new DateTime('now');

		foreach ($user->getValideurs() as $valideur) {
			if ($currentDate >= $valideur->getDateDeb() && $currentDate <= $valideur->getDateFin()) {
				$service = $valideur->getService();

				$sv[] = '<div class="flex-space-between"><div><strong>' . $service->getCode() . '</strong> - ' . $service->getLibelleCourt() . '</div><div>du ' . DateHelper::formatMyDate($valideur->getDateDeb()) . ' au <strong>' . DateHelper::formatMyDate($valideur->getDateFin()) . '</strong></div></div>';
			}
		}

		$myHTML = '<div style="background-color: rgb(192, 192, 192, 0.7);">';
		if (!empty($sv)) {
			$myHTML .= '<h5>Actuellement valideur :</h5>';
			$myHTML .= implode("", $sv);
		} else {
			$myHTML .= '<h5>Valideur d\'aucun service actuellement</h5>';
		}

		return $myHTML . '</div>';
	}
	
	/**
	 * Lister des droits effectifs de l'user en HTML
	 * @return string|NULL
	 */
	public static function droitsEffectifs(User $user): ?string
	{
		// On affiche toutes les applications transverses (qu'une seule fois)
		// Puis on affiche toutes les autres applications auxquelles a accès l'agent pour chaque service
		$transverses = [];
		$html_services = [];

		foreach ($user->getCouples() as $couple) {
			// Service associé à ce couple
			$service = $couple->getService();
			// Toutes les applications non transverses d'un service
			$profils = [];

			foreach ($couple->getApplications() as $droitEffectif) {
				$application = $droitEffectif->getApplication();
				$libelle = '<strong>' . $application->getCode() . '</strong> - ' . $application->getLibelleLong();

				if ($application->isTransverse()) {
					$transverses[] = '<div class="flex-space-between"><div>' . $libelle . '</div><div>date limite le <strong>' . DateHelper::formatMyDate($droitEffectif->getDateFin()) . '</strong></div></div>';
				} else {
					$profils[] = '<div class="flex-space-between"><div>' . $libelle . '</div><div>jusqu\'au ' . DateHelper::formatMyDate($droitEffectif->getDateFin()) . '</div></div>';
				}
			}

			// On trie les applications PROFIL par ordre alphanumérique asc
			sort($profils);

			// Si il y a des applications PROFIL dans le service
			if (!empty($profils)) {
				$html = '<div>';
				$html .= '<div class="crous-color"><strong>' . $service->getCode() . '</strong></div>';
				$html .= '<div class="crous-color">' . $service->getLibelleLong() . '</div>';
				$html .= implode("", $profils);
				$html .= '</div>';

				$html_services[] = $html;
			}
		}

		$myHTML = self::servicesWhereValidator($user);

		if(empty($transverses) && empty($html_services)) {
			$myHTML .= '<div>';
			$myHTML .= '<h5>Ne possède aucun droit pour aucune application</h5>';
		} else {
			$myHTML .= '<div class="yes-droits-effectifs-box">';
			if (!empty($transverses)) {
				$myHTML .= '<div><h5 class="crous-color">Applications transverses :</h5><div>' . implode("", array_unique($transverses)) . '</div></div>';

				if (!empty($html_services)) {
					$myHTML .= '<hr>';
				}
			}

			if (!empty($html_services)) {
				$myHTML .= '<div>';
				$myHTML .= implode("", $html_services);
				$myHTML .= '</div>';
			}
		}

		return $myHTML . '</div>';
	}

	/**
	 * Liste l'ensemble des services dans lequel se trouve un agent en HTML
	 * @return string|NULL
	 */
	public static function listServices(User $user): ?string
	{
		$servicesLibelle = [];
		foreach ($user->getServices() as $service) {
			$servicesLibelle[] = '<strong>' . $service->getCode() . '</strong> - ' . $service->getLibelleCourt();
		}

		return empty($servicesLibelle) ? NULL : implode(" / ", $servicesLibelle);
	}

	/* ----------------------------------------POUR PDF---------------------------------------- */

	/**
	 * Retourne les informations d'un agent en HTML
	 * @return string
	 */
	public static function userInfo_toPDF(User $user): string
	{
		$myHTML = '<div class="info-header">';
		$myHTML .= 		'<div style="font-size: 1.5em; font-weight:700;">' . $user->getNom() . ' ' . $user->getPrenom() . '</div>';
		$myHTML .= 		'<div>' . $user->getEmail() . '</div>';
		$myHTML .= 		'<hr>';
		$myHTML .= 		'<div>Contrat terminé le : <strong>' . DateHelper::formatMyDate($user->getDateFinValid()) . '</strong></div>';

		$listServices = self::listServices($user);
		if ($listServices === '') {
			$myHTML .= 	'<div>N\'est membre d\'aucun service</div>';
		} else {
			$myHTML .= 	'<div>Membre des services : <strong>' . $listServices . '</strong></div>';
		}
		$myHTML .= self::dsi($user);
		return $myHTML . '</div>';
	}

	/**
	 * Retourne si un agent est dsi ou non en HTML
	 * @return string
	 */
	public static function dsi_toPDF(User $user): string
	{
		if (!$user->isDSI()) {
			$myHTML = '<div><em>Ne possède pas la fonction DSI</em></div>';
		} else {
			$myHTML = '<div><em>Occupe la fonction DSI (jusqu\'au <strong>' . $user->getDateEndDSI() .'</strong>)</em>';
		}

		return $myHTML . '</div>';
	}

	/**
	 * Lister tous les services dont l'user est valideur en HTML
	 * @return string
	 */
	public static function servicesWhereValidator_toPDF(User $user): ?string
	{
		// HTML de chaque service dont il est valideur actuellement
		$sv = [];
		$currentDate = new DateTime('now');

		foreach ($user->getValideurs() as $valideur) {
			if ($currentDate >= $valideur->getDateDeb() && $currentDate <= $valideur->getDateFin()) {
				$service = $valideur->getService();

				$sv[] = '<strong>' . $service->getCode() . ' : ' . $service->getLibelleCourt() . '</strong> (jusqu\'au ' . DateHelper::formatMyDate($valideur->getDateFin()) . ')';
			}
		}

		if (!empty($sv)) {
			// $myHTML .= '<div class="box-title validator-title">Informations validateur</div>';
			$myHTML = '<p>Actuellement valideur :</p>';
			$myHTML .= implode("<br>", $sv);
		} else {
			// $myHTML .= '<div class="box-title validator-title">Informations validateur</div>';
			$myHTML = '<div>Valideur d\'aucun service actuellement</div>';
		}

		return $myHTML . '<hr>';
	}
	
	/**
	 * Lister des droits effectifs de l'user en HTML
	 * @return string|NULL
	 */
	public static function droitsEffectifs_toPDF(User $user): ?string
	{
		// On affiche toutes les applications transverses (qu'une seule fois)
		// Puis on affiche toutes les autres applications auxquelles a accès l'agent pour chaque service
		$transverses = [];
		$html_services = [];

		foreach ($user->getCouples() as $couple) {
			// Service associé à ce couple
			$service = $couple->getService();
			// Toutes les applications non transverses d'un service
			$applications = [];

			foreach ($couple->getApplications() as $droitEffectif) {
				$application = $droitEffectif->getApplication();
				$libelle = $application->getCode() . ' : ' . $application->getLibelle();

				if ($application->isTransverse()) {
					$transverses[] = '<strong>' . $libelle . '</strong>';
				} else {
					$applications[] = '<strong>' . $libelle . '</strong> jusqu\'au ' . DateHelper::formatMyDate($droitEffectif->getDateFin());
				}
			}

			// S'il y a des applications PROFIL dans le service
			if (!empty($applications)) {
				$libelle = $service->getCode() . ' : ' . $service->getLibelleCourt();
				$html = '<div>';
				$html .= '<p style="text-transform: uppercase; font-weight: 700;">' . $libelle . '</p>';
				$html .= implode("<br>", $applications);
				$html .= '</div>';

				$html_services[] = $html;
			}
		}

		$myHTML = '<div class="info-body">';
		$myHTML .= self::servicesWhereValidator_toPDF($user);

		if(empty($transverses) && empty($html_services)) {
			$myHTML .= '<div>Ne possède aucun droit pour aucune application</div>';
		} else {
			if (!empty($transverses)) {
				$myHTML .= '<p style="text-transform: uppercase; font-weight: 700;">Applications transverses :</p><div>' . implode("&nbsp; / &nbsp;", $transverses) . '</div>';

				if (!empty($html_services)) {
					$myHTML .= '<hr>';
				}
			}

			if (!empty($html_services)) {
				$myHTML .= '<div>';
				$myHTML .= implode("<hr>", $html_services);
				$myHTML .= '</div>';
			}
		}

		return $myHTML . '</div>';
	}
}