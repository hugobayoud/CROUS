<?php
namespace App\Controller;

use DateTime;
use Exception;
use Twig\Environment;
use App\Entity\Demande;
use App\Entity\Service;
use App\Helper\DateHelper;
use App\Entity\ApplicationDemande;
use App\Repository\UserRepository;
use App\Entity\ApplicationDemandes;
use App\Entity\Couple;
use App\Entity\DroitEffectif;
use App\Form\ApplicationDemandesType;
use App\Repository\DemandeRepository;
use App\Repository\ServiceRepository;
use App\Repository\ApplicationRepository;
use App\Repository\CoupleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DemandeController extends AbstractController
{
	/**
	 * Montre tous les services d'un agent pour lequel il peut faire une demande
	 * 
	 * @Route("/agent/gestion/demandes", name="agent.gestion-demandes.home")
	 * @return Response
	 */
	public function servicesList(): Response
	{
		$user = $this->getUser();
		$services = $user->getServices();

		return $this->render('agent/gestion-demandes/index.html.twig', [
			'services' => $services
		]);
	}

	// /**
	//  * Editer/créer la demande pour un service donné pour un agent
	//  * 
	//  * @Route("/agent/gestion/demandes/editer/{id}", name="agent.gestion-demandes.editer")
	//  * @return Response
	//  */
	// public function demandForOneService(Service $service, ApplicationRepository $appliRepo, DemandeRepository $demandRepo): Response
	// {
	// 	$user = $this->getUser();
	// 	$applications = $appliRepo->findAll();

	// 	if (!empty($_POST)) {
	// 		$demande = $user->getDemande($service->getId());
			
	// 		$em = $this->getDoctrine()->getManager();

	// 		// S'il n'existe pas encore de demande pour ce service pour ce user en base
	// 		if (is_null($demande)) {
	// 			$demande = new Demande();
	// 			$demande->setUser($user);
	// 			$demande->setService($service);
	// 		} else {
	// 			// Sinon, on supprime les anciennes demandes d'applications pour ce service pour cet agent dans application_demande
	// 			$rows = $demande->getApplications();
	// 			foreach ($rows as $appli_demande) {
	// 				$em->remove($appli_demande);
	// 				$em->flush();
	// 			}
	// 		}

	// 		$demande->setEtat(0);
	// 		$demande->setCreatedAt(new DateTime('now'));

	// 		$em->persist($demande);
	// 		$em->flush();
			
	// 		// On enregistre chaque application demandée dans la table application_demande
	// 		foreach ($_POST as $id => $code) {
	// 			$ad = new ApplicationDemande();
	// 			$ad->setApplication($appliRepo->findOneBy(['id' => $id]));
	// 			$ad->setDemande($demande);
	// 			$ad->setDateDeb(new DateTime('now'));
	// 			$ad->setDateFin(DateHelper::calculDateFin(new DateTime('now')));
	// 			$ad->setASupprimer(false);

	// 			$em->persist($ad);
	// 			$em->flush();
	// 		}

	// 		$this->addFlash('message', 'Demande enregistrée avec succès');
	// 		return $this->redirectToRoute('agent.gestion-demandes.home');
	// 	}

	// 	return $this->render('agent/gestion-demandes/edit.html.twig', [
	// 		'applications' => $applications,
	// 		'service' => $service
	// 	]);
	// }

	
	/**
	 * Editer/créer la demande pour un service donné pour un agent
	 * 
	 * @Route("/agent/gestion/demandes/editer/{id}", name="agent.gestion-demandes.editer")
	 * @return Response
	 */
	public function demandForOneService(Service $service, ApplicationRepository $appliRepo): Response
	{
		$user = $this->getUser();
		$applications = $appliRepo->findAll();
		$demande = $user->getDemande($service->getId());

		if (!empty($_POST)) {
			$now = new DateTime('now');
			$em = $this->getDoctrine()->getManager();

			// S'il n'existe pas encore de demande pour ce service pour ce user en base on la crée
			if (is_null($demande)) {
				$demande = new Demande();
				$demande->setUser($user);
				$demande->setService($service);
			}

			// TO-DO : On ajout les ressources supplémentaires à la demande
			$demande->setEtat(0);
			$demande->setCreatedAt($now);

			$em->persist($demande);
			$em->flush();
			
			//On supprime les anciennes demandes d'applications pour cette demande dans APPLICATION_DEMANDE
			foreach ($demande->getApplications() as $appli_demande) {
				$em->remove($appli_demande);
				$em->flush();
			}

			// On enregistre chaque application demandée dans la table APPLICATION_DEMANDE
			// Seule les applications dont l'agent n'a pas encore les droits sont ajoutées à la demande
			// Le reste se trouve déjà dans DROIT_EFFECTIF
			dd($_POST);
			foreach ($_POST as $id => $code) {
				$ad = new ApplicationDemande();

				$ad->setApplication($appliRepo->findOneBy(['id' => $id]));
				$ad->setDemande($demande);
				$ad->setDateDeb($now);
				$ad->setDateFin(DateHelper::calculDateFin($now));
				// Ici toutes les applications sont de nouveaux droits à ajouter, aucune n'est à supprimer
				$ad->setASupprimer(false);

				$em->persist($ad);
				$em->flush();
			}

			$this->addFlash('message', 'Demande enregistrée avec succès');
			return $this->redirectToRoute('agent.gestion-demandes.home');
		}

		return $this->render('agent/gestion-demandes/edit.html.twig', [
			'applications' => $applications,
			'service' => $service,
			'demande' => $demande
		]);
	}

	
	/**
	 * Validations des demandes. Affichage de tous les services dont l'user est valideur. 
	 * Ne doit pas accèder à cette page s'il est valideur dans aucun service
	 * 
	 * @Route("valideur/validation/demandes", name="valideur.validation-demandes.home")
	 */
	public function validateDemandsHome()
	{
		$user = $this->getUser();

		if ($user->isAValidator()) {
			$services = $user->getServicesWhereValidator();
			return $this->render('valideur/validation-demandes/index.html.twig', [
				'services' => $services
			]);

		} else {
			$this->addFlash('warning', "ACCES REFUSE : Vous n'êtes valideur d'aucun service.");
			return $this->redirectToRoute('home');
		}
	}

	/**
	 * Valider les demande d'un service en tant que valideur
	 * 
     * @Route("valideur/validation/demandes/{id}", name="valideur.validation-demandes.service")
    */
	public function approveDemandsService(Request $request, Service $service)
	{
		$currentUser = $this->getUser();

		if ($currentUser->isValidator($service->getId())) {
			// On récupère les demandes de ce service dont l'état vaut 0
			$demandes = $service->getToApproveDemands();

			$forms = [];
			foreach ($demandes as $demande) {
				$applicationDemandes = new ApplicationDemandes();
				foreach ($demande->getApplications() as $application) {
					// Application demandée par l'agent de la demande ajoutée dans le formulaire
					$applicationDemandes->getApplicationDemandes()->add($application);
				}
	
				// On crée un form du tableau de ApplicationDemandes pour chaque demande différente
				$forms[] = $this->createForm(ApplicationDemandesType::class, $applicationDemandes);
			}

			// Si le formulaire est envoyé, on récupère l'id correspondant à l'agent et les modifications à faire en BDD
			if (isset($_POST["custId"])) {
				$index = $_POST["custId"];
				// On handleRequest() sur le bon formulaire
				$form = $forms[$index]->handleRequest($request);

				// Si le form est soumis et valide
				if ($form->isSubmitted() && $form->isValid()) {
					$em = $this->getDoctrine()->getManager();
									
					// SUPPRESION EN BASE DES ANCIENNES DEMANDES DAPPLICATION FAITE PAR LAGENT
					// POUR LES REMPLACER PAR CELLE VALIDES PAR LE VALIDEUR
					foreach ($demandes[$index]->getApplications() as $applicationDemande) {
						$em->remove($applicationDemande);
						$em->flush();
					}
		
					// AJOUT EN BASE DES NOUVELLES PERIODES
					foreach ($form->getData()->getApplicationDemandes() as $applicationDemande) {
						$applicationDemande->setDemande($demandes[$index]);
						$applicationDemande->getDateFin()->setTime(23, 59, 59);
						$applicationDemande->setASupprimer(false);

						$em->persist($applicationDemande);
						$em->flush();
					}

					$demandes[$index]->setEtat(1);
					$em->persist($demandes[$index]);
					$em->flush();
		
					// On notifie que tout s'est bien passé
					$this->addFlash('message', "Validation de la demande bien enregistrée.");
					return $this->redirectToRoute('valideur.validation-demandes.service', [
						'id' => $service->getId()
					]);
				}
			}

			$renderedForms = [];
			foreach ($forms as $form) {
				$renderedForms[] = $form->createView();
			}

			return $this->render('valideur/validation-demandes/service.html.twig', [
				'service' => $service,
				'demandes' => $demandes,
				'forms' => $renderedForms
			]);
		} else {
			$this->addFlash('warning', "acces refusé, vous n'êtes pas valideur de ce service");
			return $this->redirectToRoute('valideur.validation-demandes.home');
		}
	}

	/**
	 * Affichage de toutes les demandes à traiter pour un admin/dsi (état 1 -> état 2)
	 * @Route("/admin/gestion-demandes", name="admin.gestion-demandes")
	 */
	public function administerDemandsDSI(DemandeRepository $demandRepo, CoupleRepository $coupleRepo)
	{
		// On récupère les demandes à l'état 1
		$demandes = $demandRepo->findBy(['etat' => 1]);

		if (isset($_POST['demande_id'])) {
			// Pour les applications dans "droits à ajouter", on les ajoutent en base droitEffectif
			// Pour les applications dans "droits à supprimer", on les enlève de la base droitEffectif si elles existaient bien avant
			// Pour les applications dans "droits inchangés", on ne touche à rien

			$demande = $demandRepo->findOneBy(['id' => $_POST['demande_id']]);
			$user = $demande->getUser();
			$service = $demande->getService();
			
			$em = $this->getDoctrine()->getManager();
			
			$couple = $coupleRepo->findOneBy(['user' => $user, 'service' => $service]);
			if(is_null($couple)) {
				$couple = new Couple();
				$couple->setUser($user);
				$couple->setService($service);
				$em->persist($couple);
				$em->flush();
			}

			foreach ($demande->getApplications() as $applicationDemande) {
				if ($applicationDemande->needNewAccess() && !$applicationDemande->getASupprimer()) {
					$droitEffectif = new DroitEffectif();
					$droitEffectif->setCouple($couple);
					$droitEffectif->setApplication($applicationDemande->getApplication());
					$droitEffectif->setDateDeb($applicationDemande->getDateDeb());
					$droitEffectif->setDateFin($applicationDemande->getDateFin());

					$em->persist($droitEffectif);
					$em->flush();
				} else if ($applicationDemande->getASupprimer()) {
					foreach ($couple->getApplications() as $droitEffectif) {
						if ($droitEffectif->getApplication() === $applicationDemande->getApplication()) {
							$em->remove($droitEffectif);
							$em->flush();
						}
					}
				}
				
				// On supprime toutes les lignes dans application_demande car les droits ont bien été traités.
				$em->remove($applicationDemande);
				$em->flush();
			}

			$demande->setEtat(2);
			$em->persist($demande);
			$em->flush();

			$this->addFlash('message', 'Drtois correctement modifiés pour l\'agent ' . $user->getPrenom() . ' ' . $user->getNom());
			return $this->redirectToRoute('admin.gestion-demandes');
		}

		return $this->render('admin/gestion-demandes/index.html.twig', [
			'demandes' => $demandes
		]);
	}
}