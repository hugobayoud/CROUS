<?php
namespace App\Controller;

use Exception;
use Twig\Environment;
use App\Entity\Demande;
use App\Entity\Service;
use App\Repository\DemandeRepository;
use App\Repository\ServiceRepository;
use App\Repository\ApplicationRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DemandeController extends AbstractController
{
	/**
	 * Montre tous les services d'un agent pour lequel il peut faire une demande
	 * 
	 * @Route("/user/demandes", name="user.demandes")
	 * @return Response
	 */
	public function servicesList(UserInterface $currentUser): Response
	{
		$services = $currentUser->getServices();

		return $this->render('user/demande/demandes.html.twig', [
			'services' => $services
		]);
	}

	/**
	 * @Route("/user/demande/{id}", name="user.demande.edit")
	 * @return Response
	 */
	public function demandForOneService(UserInterface $currentUser, Service $service, UserRepository $userRepo, ApplicationRepository $appliRepo, DemandeRepository $demandRepo): Response
	{
		// On récupère l'ensemble des applications
		$applicationsList = $appliRepo->findAll();
		// On récupère la demande pour cet agent pour ce service en base si elle existe
		$demande =  $demandRepo->findOneBy([
			'user' => (int)$currentUser->getId(), 
			'service' => (int)$service->getId()
		]);

		// Si il n'y a encore pas de demande associée à ce service pour cet agent dans la BDD, on en créé une
		if ($demande === NULL) {
			$demande = new Demande();
			$demande->setUser($currentUser);
			$demande->setService($service);
			$demande->setEtat(0);
		}

		// On récupère les applications de l'agent (peut etre null)
		$applications_agent = $demande->getNewApplications();

		// Si formulaire envoyé, on le traite
		if (!empty($_POST)) {
			// On écrase l'ancienne sélection par la nouvelle
			$demande->setNewApplications($_POST);

			$demande->setCreatedAt(new DateTime('now'));
			try {
				$em = $this->getDoctrine()->getManager();
				$em->persist($demande);
				$em->flush();
			} catch(Exception $e) {
				$this->addFlash('warning', "Erreur : la demande n'a pas pu être enregistrée. Veuillez réessayer. Si le problème persiste, veuillez contacter un agent de la DSI");
			}

			$this->addFlash('message', 'Demande pour ce service (' . $service->getLibelleCourt() . ') bien enregistrée');
			return $this->redirectToRoute('user.demandes');
		}

		return $this->render('user/demande/edit.html.twig', [
			'service' => $service,
			'applications' => $applicationsList,
			'applications_agent' => $applications_agent,
			'etat' => $demande->getEtat()
		]);
	}
}