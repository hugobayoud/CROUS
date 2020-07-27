<?php
namespace App\Controller;

use DateTime;
use App\Entity\Couple;
use App\Entity\Demande;
use App\Entity\Service;
use App\Helper\DateHelper;
use App\Helper\ProtoHelper;
use App\Entity\DroitEffectif;
use App\Entity\ApplicationDemande;
use App\Entity\ApplicationDemandes;
use App\Repository\CoupleRepository;
use App\Form\ApplicationDemandesType;
use App\Repository\DemandeRepository;
use App\Repository\ApplicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

			//On ajoute les ressources supplémentaires (formatées) à la demande 
			$demande->setTelephone(ProtoHelper::formatMyPhoneNumber($_POST['phone']));
			$demande->setMailDe(ProtoHelper::formatMyMails($_POST['mailTo']));
			$demande->setRepertoiresServeur(ProtoHelper::formatMyFolders($_POST['folders']));
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
			foreach ($_POST as $id => $appli) {
				if (is_int($id)) {
					$ad = new ApplicationDemande();

					$applicationNeeded = array_filter(
						$applications,
						function ($e) use (&$id) {
							return $e->getId() == $id;
						}
					);

					$ad->setApplication($applicationNeeded[array_key_first($applicationNeeded)]);
					$ad->setDemande($demande);
					$ad->setDateDeb($now);
					$ad->setDateFin(DateHelper::calculDateFin($now));
					// Ici toutes les applications sont de nouveaux droits à ajouter, aucune n'est à supprimer
					$ad->setASupprimer(false);
	
					$em->persist($ad);
					$em->flush();
				}
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
	 * Valider les demandes d'un service en tant que valideur
	 * 
     * @Route("valideur/validation/demandes/{id}", name="valideur.validation-demandes.service")
    */
	public function approveDemandsService(Request $request, Service $service)
	{
		$currentUser = $this->getUser();

		if ($currentUser->isValidator($service->getId())) {
			// On récupère les demandes de ce service dont l'état vaut 0 ou 1
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
					foreach ($demandes[$index]->getApplications() as $application_demande) {
						$em->remove($application_demande);
						$em->flush();
					}
		
					// AJOUT EN BASE DES NOUVELLES PERIODES
					foreach ($form->getData()->getApplicationDemandes() as $application_demande) {
						$application_demande->setDemande($demandes[$index]);
						$application_demande->getDateFin()->setTime(23, 59, 59);
						$application_demande->setASupprimer(false);

						$em->persist($application_demande);
						$em->flush();
					}

					$demandes[$index]->setEtat(1);
					//On ajoute les ressources supplémentaires (formatées) à la demande
					$demandes[$index]->setTelephone(ProtoHelper::formatMyPhoneNumber($_POST['phone']));
					$demandes[$index]->setMailDe(ProtoHelper::formatMyMails($_POST['mailTo']));
					$demandes[$index]->setRepertoiresServeur(ProtoHelper::formatMyFolders($_POST['folders']));

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
	 * Il y a n"anmoins deux types de demandes à l'état 1 :
	 * 		- la demande qui est validée par un valideur qui va faire l'objet de tout nouveaux droits effectifs pour l'agent
	 * 		- la demande qui provient de modifications par un valideur sur des droits effectifs déjà effectifs
	 * La différence entre les deux se tient sur le champ "prioritaire" dans la table DEMANDE
	 *  
	 * @Route("/admin/gestion-demandes", name="admin.gestion-demandes")
	 */
	public function administerDemandsDSI(DemandeRepository $demandRepo, CoupleRepository $coupleRepo)
	{
		// On récupère les demandes à l'état 1
		$demandes = $demandRepo->findBy(['etat' => 1]);

		// On tri les demandes en deux groupes (cf. doc au dessus de la signature)
		$demandesOriginales = $demandesPrioritaires = [];
		foreach ($demandes as $demande) {
			if ($demande->isPrioritaire()) {
				$demandesPrioritaires[] = $demande;
			} else {
				$demandesOriginales[] = $demande;
			}
		}

		if (isset($_POST['demande_id'])) {
			// Pour les applications dans "droits à ajouter", on les ajoute en base le droit effectif
			// Pour les applications dans "droits à supprimer", on les enlève de la base le droit effectif si elles existaient bien avant
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
			}
			
			// On associe les nouvelles ressources supplémentaires à la demande
			$couple->setTelephone($demande->getTelephone());
			$couple->setMailDe($demande->getMailDe());
			$couple->setRepertoiresServeur($demande->getRepertoiresServeur());
			$em->persist($couple);
			$em->flush();

			// Pour chaque droit effectif, on supprime s'il est a supprimer, on change la date de fin si elle est a changer
			foreach ($couple->getApplications() as $droit_effectif) {
				if ($droit_effectif->hasToBeDeleted()) {
					$em->remove($droit_effectif);
				} else if ($droit_effectif->hasToBePostponed()) {
					$droit_effectif->setDateFin($droit_effectif->getNouvelleEcheance());
					$droit_effectif->setNouvelleEcheance(NULL);
					$droit_effectif->setStatus(NULL);
					$em->persist($droit_effectif);
				}
				$em->flush();
			}

			// Application provenant de la demande qui sont obligatoirement à ajouter comme droit effectif
			foreach ($demande->getApplications() as $application_demande) {
				$droit_effectif = new DroitEffectif();
				$droit_effectif->setCouple($couple);
				$droit_effectif->setApplication($application_demande->getApplication());
				$droit_effectif->setDateDeb($application_demande->getDateDeb());
				$droit_effectif->setDateFin($application_demande->getDateFin());
				
				// On supprime l'application_demande car le droit associé a bien été traité
				$em->remove($application_demande);
				$em->flush();
			}

			// On passe la demande à l'état 2 (traitée par la DSI)
			$demande->setEtat(2);
			// On enlève les ressources supplémentaires de la demande qui ont bien été traitées
			$demande->setTelephone(NULL);
			$demande->setMailDe(NULL);
			$demande->setRepertoiresServeur(NULL);
			$demande->setPrioritaire(0);
			$em->persist($demande);
			$em->flush();

			$this->addFlash('message', 'Droits correctement modifiés pour l\'agent ' . $user->getPrenom() . ' ' . $user->getNom());
			return $this->redirectToRoute('admin.gestion-demandes');
		}

		return $this->render('admin/gestion-demandes/index.html.twig', [
			'demandesPrioritaires' => $demandesPrioritaires,
			'demandesOriginales' => $demandesOriginales
		]);
	}
}