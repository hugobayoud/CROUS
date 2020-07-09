<?php
namespace App\Helper;

use DateTime;
use App\Entity\User;
use App\Helper\DateHelper;

class HtmlHelper {
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

				$sv[] = '<strong>' . $service->getCode() . ' : ' . $service->getLibelleCourt() . '</strong> (jusqu\'au ' . DateHelper::formatMyDate($valideur->getDateFin()) . ')';
			}
		}

		if (!empty($sv)) {
			$myHTML = '<div class="yes-validator-box">';
			$myHTML .= '<div class="box-title validator-title">Informations validateur</div>';
			$myHTML .= '<p>Actuellement valideur :</p>';
			$myHTML .= implode("<br>", $sv);
		} else {
			$myHTML = '<div class="no-validator-box">';
			$myHTML .= '<div class="box-title validator-title">Informations validateur</div>';
			$myHTML .= 'Valideur d\'aucun service actuellement';
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
			$applications = [];

			foreach ($couple->getApplications() as $droitEffectif) {
				$application = $droitEffectif->getApplication();
				$libelle = $application->getCode() . ' : ' . $application->getLibelle();

				if ($application->isTransverse()) {
					$transverses[] = '<strong>' . $libelle . '</strong>';
				} else {
					$applications[] = '<div><strong>' . $libelle . '</strong> jusqu\'au ' . DateHelper::formatMyDate($droitEffectif->getDateFin()) . '</div>';
				}
			}

			// Si il y a des applications PROFIL dans le service
			if (!empty($applications)) {
				$html = '<div class="one-service-div">';
				$html .= '<p>' . $service->getLibelleCourt() . '</p>';
				$html .= implode(" ", $applications);
				$html .= '</div>';

				$html_services[] = $html;
			}
		}

		if(empty($transverses) && empty($html_services)) {
			$myHTML = '<div class="no-droits-effectifs-box">';
			$myHTML .= '<div class="box-title droits-effectifs-title">Droits effectifs</div>';
			$myHTML .= '<div>Ne possède aucun droit pour aucune application</div>';
		} else {
			$myHTML = '<div class="yes-droits-effectifs-box">';
			$myHTML .= '<div class="box-title droits-effectifs-title">Droits effectifs</div>';
			if (!empty($transverses)) {
				$myHTML .= '<div class="transverses-div"><p>Applications transverses :</p><div>' . implode("&nbsp; / &nbsp;", $transverses) . '</div></div>';
			}

			if (!empty($html_services)) {
				$myHTML .= '<div class="profils-div">';
				$myHTML .= implode("<hr>", $html_services);
				$myHTML .= '</div>';
			}
		}

		return $myHTML . '</div>';
	}

	/**
	 * Retourne si un agent est dsi ou non en HTML
	 * @return string
	 */
	public static function dsi(User $user): string
	{
		if (!$user->isDSI()) {
			$myHTML = '	<div class="no-dsi-box">';
			$myHTML .= '<div class="box-title dsi-title">Informations DSI</div>';
			$myHTML .= '<div>Ne possède pas la foction DSI</div>';
		} else {
			$myHTML = '<div class="yes-dsi-box">';
			$myHTML .= '<div class="box-title dsi-title">Informations DSI</div>';
			$myHTML .= 'Occupe la fonction DSI (jusqu\'au <strong>' . $user->getDateEndDSI() .'</strong>)';
		}

		return $myHTML . '</div>';
	}

	/**
	 * Retourne les informations d'un agent en HTML
	 * @return string
	 */
	public static function userInfo(User $user): string
	{
		$myHTML = '<div class="user-info-box">';
		$myHTML .= '<div class="box-title user-info-title">Informations agent</div>';
		$myHTML .= '<div>';
		$myHTML .= '<div>' . $user->getNom() . ' ' . $user->getPrenom() . '</div>';
		$myHTML .= '<div>' . $user->getEmail() . '</div>';
		$myHTML .= '</div>';
		$myHTML .= '<div class="text-muted">Contrat terminé le : </div><div class="user-info-services">' . DateHelper::formatMyDate($user->getDateFinValid()) . '</div>';
		$myHTML .= '<div>';

		$listServices = self::listServices($user);
		if ($listServices === '') {
			$myHTML .= '<div class="text-muted">N\'est membre d\'aucun service</div>';
		} else {
			$myHTML .= '<div class="text-muted">Membre des services : </div><div class="user-info-services">' . $listServices . '</div>';
		}

		return $myHTML . '</div></div>';
	}

	/**
	 * Liste l'ensemble des services dans lequel se trouve un agent en HTML
	 * @return string|NULL
	 */
	public static function listServices(User $user): ?string
	{
		$servicesLibelle = [];
		foreach ($user->getServices() as $service) {
			$servicesLibelle[] = $service->getLibelleCourt();
		}

		return empty($servicesLibelle) ? NULL : implode(" / ", $servicesLibelle);
	}
}