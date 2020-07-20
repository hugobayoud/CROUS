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
	 * Formater un string pour les noms des répertoires auxquels à accès l'agent
	 * Formatage léger de la chaine car peut d'informations sur comment l'utilisateur peut écrire.
	 * @return string|NULL
	 * @param $delimiters : delimiter a supprimer pour les remplacer par ';'
	 */
	public static function formatMyFolders(string $folders, array $delimiters = [',', ':', '.', '/', '|', '\\']): ?string
	{
		if (is_null($folders)) {
			return NULL;
		}

		// On enlève les possibles balises html
		$myFolders = strip_tags($folders);

		// On suppose que l'utilisateur a séparé les différents dossiers par ',' ';' ':' '.' '/' '|' ou '\' que l'on veut remplacer par ';'
		$parts = $myFolders;
		foreach ($delimiters as $delimiter) {
			$parts = implode(';', explode($delimiter, $parts));
		}

		// On supprime les ";" et espaces doublons pour n'en garder qu'un
		$parts = self::getRideOfDuplicates($parts);

		return $parts;
	}

	/**
	 * Formatage pour liste de mails
	 * @return string|NULL
	 */
	public static function formatMyMails(string $mails): ?string
	{
		$mails = strtolower($mails);
		// On le formate comme pour les répertoires mais on va s'assure que chaque élement a le motif d'une adresse mail
		$mails = self::formatMyFolders($mails, [',', ':', '/', '|', '\\', ' ']);

		if (is_null($mails)) {
			return NULL;
		}

		// On récupère chaque élement comme élement d'un tableau
		$parts = explode(';', $mails);
		$goodParts = [];

		foreach ($parts as $part) {
			if (preg_match("/[a-z0-9._-]+@[a-z0-9._-]+\.[a-z]{2,5}$/", $part) === 1) {
				$goodParts[] = $part;
			}
		}

		if (empty($goodParts)) {
			return NULL;
		}
		
		return implode(';', $goodParts);
	}

	/**
	 * Supprimer les doublons
	 */
	public static function getRideOfDuplicates(string $myString): ?string
	{
		// Supprimer tous les espaces
		$myString = preg_replace("/ */", "", $myString);
		// Séparer tous les fichiers d'un "; "
		$myString = preg_replace("/;+/", "; ", $myString);

		return $myString;
	}
}