<?php

namespace App\Controller;

use DateTime;
use App\Entity\Demande;
use App\Entity\Service;
use App\Data\SearchData;
use App\Form\SearchForm;
use App\Entity\Valideurs;
use App\Form\ValideursType;
use App\Helper\ProtoHelper;
use App\Repository\DemandeRepository;
use App\Repository\ServiceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/valideur", name="valideur.")
 * 
 * @IsGranted("ROLE_A_VALIDATOR")
 */
class ValideurController extends AbstractController
{
	/**
	 * Home pour un valideur (quelqu'un qui est valideur d'un ou plusieurs service(s))
	 * @Route("/", name="home")
	 */
	public function index(DemandeRepository $demandRepo)
	{
		$user = $this->getUser();

		if (!$user->isAValidator()) {
			return $this->redirectToRoute('home');
		}

		return $this->render('valideur/index.html.twig');
	}

	/**
	 * On affiche tous les services de l'user et il choisit dans quel service il souhaite faire une modification de valideur
	 * Trois types de personnes :
	 * 		- C'est un dsi en cours => il peut aller dans tous les services et nommer n'importe quel agent de ce service valideur
	 * 		- C'est un agent mais il a le pouvoir valideur en cours dans ce service alors il peut promouvoir qui il souhaite valideur
	 * 		- C'est un agent et il n'a pas de pouvoir valideur mais il peut regarder qui est valideur sans rien pouvoir modifier
	 * 
     * @Route("/gestion/valideurs", name="gestion-valideurs.home")
    */
	public function servicesList(ServiceRepository $serviceRepo, Request $request)
	{
		$user = $this->getUser();

		$data = new SearchData();
		$form = $this->createForm(SearchForm::class, $data);
		$form->handleRequest($request);

		if ($user->isAdmin() || $user->isDSI()) {
			// Si l'user est DSI alors il a accès à tous les services, qu'il soit valideur ou non
			$services = $serviceRepo->findSearch($data);
		} else {
			// Sinon, on ne lui affiche que les services dont il fait partie (qu'il soit valideur ou non)
			$services = $user->getServices();
		}

		return $this->render("valideur/gestion-valideurs/index.html.twig", [
			'services' => $services,
			'form' => $form->createView()
		]);
	}

	/**
	 * Page de gestion des valideurs pour modification des droits en tant que valideur d'un service
	 * Un DSI peut se mettre des droits valideur
	 * Un valideur qui n'est pas DSI ne peut pas s'ajouter des droits valideur
	 * 
     * @Route("/gestion/valideurs/{id}", name="gestion-valideurs.service")
    */
	public function administerValidators(Request $request, Service $service)
	{
		$currentUser = $this->getUser();

		if ($currentUser->isAdmin() || $currentUser->isDSI() || $currentUser->isValidator($service->getId())) {
			if ($currentUser->isDSI()) {
				// On récupère les agents de ce service (dont leur compte ont été validé)
				$users = $service->getValidatedUsers();
			} else {
				// Si il n'est pas DSI, on enleve l'user du tableau
				$users = $service->getValidatedUsers($currentUser);
			}
			$forms = [];

			foreach ($users as $user) {
				$valideurs = new Valideurs();
				foreach ($user->getValideurs() as $valideur) {
					// Si cette période correspond bien au service actuel
					if ($valideur->getService()->getId() === $service->getId()) {
						$valideurs->getValideurs()->add($valideur);
					}
				}
				// On crée un form du tableau de DSIs d'un user pour chaque user
				$forms[] = $this->createForm(ValideursType::class, $valideurs);
			}

			// Si le formulaire est envoyé, on récupère l'id correspondant à l'agent et les modifications à faire en BDD
			if (isset($_POST["custId"])) {
				$index = $_POST["custId"];
				$form = $forms[$index]->handleRequest($request);

				// Si le form est soumis et valide
				if ($form->isSubmitted() && $form->isValid()) {
					$em = $this->getDoctrine()->getManager();

					// SUPPRESION EN BASE DES ANCIENNES PERIODES
					foreach ($users[$index]->getValideurs() as $valideur) {
						if ($valideur->getService()->getId() === $service->getId()) {
							$em->remove($valideur);
							$em->flush();
						}
					}
		
					// AJOUT EN BASE
					foreach ($form->getData()->getValideurs() as $valideur) {
						$valideur->setUser($users[$index]);
						$valideur->setService($service);
						$valideur->getDateFin()->setTime(23, 59, 59);
						$em->persist($valideur);
						$em->flush();
					}
		
					// On notifie que tout s'est bien passé
					$this->addFlash('message', "Modifications enregistrées avec succès pour l'agent " . $users[$index]->getPrenom() . " " . $users[$index]->getNom() . ".");
					return $this->redirectToRoute('valideur.gestion-valideurs.service', [
						'id' => $service->getId()
					]);
				}
			}

			$renderedForms = [];
			foreach ($forms as $form) {
				$renderedForms[] = $form->createView();
			}

			return $this->render('valideur/gestion-valideurs/service.html.twig', [
				'service' => $service,
				'users' => $users,
				'forms' => $renderedForms, 
			]);
		} else {
			$this->addFlash('warning', "acces refusé");
			return $this->redirectToRoute('valideurs.home');
		}
	}
		
	/**
	 * On affiche tous les services ou l'user est valideur pour qu'il puisse faire de la revue de droits quand les droits arrivent bientôt à expiration
	 * 
     * @Route("/revue-droits", name="revue-droits.home")
	 */
	public function checkAccessHome()
	{
		$user = $this->getUser();

		if ($user->isAValidator()) {
			$services = $user->getServicesWhereValidator();
			return $this->render('valideur/revue-droits/index.html.twig', [
				'services' => $services
			]);

		} else {
			$this->addFlash('warning', "ACCES REFUSE : Vous n'êtes valideur d'aucun service.");
			return $this->redirectToRoute('home');
		}
	}

	/**
	 * Page de revue des droits pour un service dont l'user est valideur. Il peut connaître toutes les autorisations accordées pour les agents de son service, les modifier, les supprimer
	 * 
     * @Route("/revue-droits/{id}", name="revue-droits.service")
    */
	public function administeAccessForOneService(Request $request, Service $service)
	{
		$currentUser = $this->getUser();

		if ($currentUser->isValidator($service->getId())) {
			// On récupère tous les les agents de ce service (dont leur compte ont été validé)
			$users = $service->getValidatedUsers();

			// Si le formulaire est envoyé en post
			if (isset($_POST['custId'])) {
				// On récupère le user
				$user = $users[$_POST['custId']];
				// Le couple associé
				$couple = $user->getCouple($service->getId());
				$em = $this->getDoctrine()->getManager();

				foreach ($_POST as $key => $v) {
					if (is_int($key)) {
						// On récupère le droit_effectif associé à l'application
						$droit_effectif = $couple->getApplication($key);

						if ($v === 's') {
							// On passe le champ "status" à 's'
							$droit_effectif->setStatus('s');
						} else {
							// On a modification de date, on rempli le champ "nouvelle_date" dans DROIT_EFFECTIF
							$droit_effectif->setNouvelleEcheance(new DateTime($v . '23:59:59'));
							// On passe le champ "status" à 'c'
							$droit_effectif->setStatus('c');
						}

						$em->persist($droit_effectif);
						$em->flush();
					}
				}

				// On formate les string des ressources supp.
				$phone = ProtoHelper::formatMyPhoneNumber($_POST['phone']);
				$mailTo = ProtoHelper::formatMyMails($_POST['mailTo']);
				$folders = ProtoHelper::formatMyFolders($_POST['folders']);

				// Si aucune modification n'a été apportée aux ressources supp, on ne crée pas de nouvelle demande
				if ($phone !== $couple->getTelephone() || $mailTo !== $couple->getMailDe() || $folders !== $couple->getRepertoiresServeur()) {
					$demande = $user->getDemande($service->getId());

					// S'il n'existe pas encore de demande pour ce service pour cet agent en cours on la crée
					if (is_null($demande)) {
						$demande = new Demande();
						// On ajoute le (user, service)
						$demande->setUser($user);
						$demande->setService($service);
					}

					// On ajoute les ressources supplémentaires (formatées) à la demande
					$demande->setTelephone($phone);
					$demande->setMailDe($mailTo);
					$demande->setRepertoiresServeur($folders);
					// Champs qui ne peuvent être null
					$demande->setEtat(1);
					$demande->setCreatedAt(new DateTime('now'));
					// On rend prioritaire la demande = l'agent ne peut modifier les ressources supp
					$demande->setPrioritaire(true);
	
					$em->persist($demande);
					$em->flush();

					$this->addFlash('message', "Modifications enregistrées avec succès");
				}
			// Si la demande de modification des droits effectifs est supprimée par un valideur
			} else if (isset($_POST['suppID'])) {
				// On récupère le user
				$user = $users[$_POST['suppID']];
				// Le couple associé
				$couple = $user->getCouple($service->getId());
				$em = $this->getDoctrine()->getManager();

				// On supprime les modifications sur les ressources supplémentaires
				$demande = $user->getDemande($service->getId());
				$demande->setTelephone(NULL);
				$demande->setMailDe(NULL);
				$demande->setRepertoiresServeur(NULL);
				$demande->setPrioritaire(false);
				$em->persist($demande);
				$em->flush();

				// On ne prend plus en compte les modifications faites sur les droits effectifs
				foreach ($couple->getApplications() as $droit_effectif) {
					$droit_effectif->setNouvelleEcheance(NULL);
					$droit_effectif->setStatus(NULL);
					$em->persist($demande);
					$em->flush();
				}

				$this->addFlash('message', "Modifications supprimées avec succès");
			}

			return $this->render('valideur/revue-droits/service.html.twig', [
				'service' => $service,
				'users' => $users
			]);
		} else {
			$this->addFlash('warning', "ACCES INTERDIT : Vous n'êtes pas responsable de ce service.");
			return $this->redirectToRoute('valideurs.home');
		}
	}
}