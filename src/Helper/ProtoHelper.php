<?php
namespace App\Helper;


class ProtoHelper 
{
	/**
	 * Formater un numéro de téléphone en faisant plusieurs vérification sur la chaine
	 * @return string|NULL
	 */
	public static function formatMyPhoneNumber(string $phone): ?string
	{
		if (is_null($phone)) {
			return NULL;
		}

		// On enlève tous les espaces possibles dans le mot de passe
		$myNumber = preg_replace('/\s+/', '', $phone);
		$length = strlen($myNumber);

		// Si longueur = 10, on renvoie le numéro tel quel
		if ($length === 10) {
			return $myNumber;
		}
		// Si la longueur du numéro vaut moins de 8 ou plus de 10, le numéro est obligatoirement mauvis, on renvoie null
		if ($length < 7 || $length > 10) {
			return NULL;
		} else {
			// si length = 8, nécessairement on n'a pas le 0 au début ni le chiffre ensuite (04,06,07, ..), on renvoie les 8 chiffres précédés d'un "0X"
			if ($length === 8) {
				return '0X' . $myNumber;
				// si length = 9, nécessairement le 0 à été oublié, on renvoie les 9 chiffres précédés d'un "0"
			} else if ($length === 9) {
				return '0' . $myNumber;
			}
		}

		// Arrivé ici : length=10, nécessairement le numéro est sous la forme 0623568754
		return $myNumber;
	}
	
	/**
	 * Formatage pour liste de mails
	 * @return string|NULL
	 */
	public static function formatMyMails(string $mails): ?string
	{
		$parts = self::preFormatedData($mails, TRUE);

		$goodParts = [];
		foreach ($parts as $part) {
			if (preg_match("/[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,5}$/", $part) === 1) {
				$goodParts[] = '"' . $part . '"';
			}
		}

		return empty($goodParts) ? NULL : implode('; ', $goodParts);
	}

	/**
	 * Formater un string pour les noms des répertoires auxquels à accès l'agent
	 * Formatage léger de la chaine car peut d'informations sur comment l'utilisateur peut écrire.
	 * @return string|NULL
	 * @param $delimiters : delimiter a supprimer pour les remplacer par ';'
	 */
	public static function formatMyFolders(string $folders): ?string
	{
		// array $delimiters = [',', ':', '.', '/', '|', '\\']
		$parts = self::preFormatedData($folders);

		$good_parts = [];
		foreach ($parts as $part) {
			if (preg_match("/[a-z0-9._-]+$/", $part) === 1) {
				$good_parts[] = '"' . $part . '"';
			}
		}

		return empty($good_parts) ? NULL : implode('; ', $good_parts);
	}

	/**
	 * Pré-formater la chaine de caractères avant traitement spécifique
	 * @param string $data 	: chaine de caractères à pré-formater
	 * @param bool $toLower : si la chaîne doit être en minuscule ou non 
	 * @return array
	 */
	public static function preFormatedData(string $data, bool $toLower = FALSE): array
	{
		// Si le champs n'est pas rempli, on renvoie null
		if (is_null($data) || empty(trim($data))) {
			return [];
		}

		// On enlève les possibles balises html
		$data = strip_tags($data);

		// On met tout en minuscule
		if ($toLower) {
			$data = strtolower($data);
		}

		// On enlève tous les espaces blancs
		$data = trim($data);

		// On récupère chaque élement comme élement d'un tableau
		return explode('"', $data);
	}
}