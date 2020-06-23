<?php
namespace App\Controller;

use Twig\Environment;
use App\Entity\Demande;
use App\Entity\Service;
use App\Repository\DemandeRepository;
use App\Repository\ServiceRepository;
use App\Repository\ApplicationRepository;
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
	public function servicesList(UserInterface $currentUser, ServiceRepository $serviceRepo): Response
	{
		$services = $serviceRepo->findAllByUserId($currentUser->getId());
		return $this->render('user/demande/demandes.html.twig', [
			'services' => $services
		]);
	}

		/**
	 * @Route("/user/demande/{id}", name="user.demande.edit")
	 * @return Response
	 */
	public function demandForOneService(UserInterface $currentUser, Service $service, ApplicationRepository $appliRepo, DemandeRepository $demandRepo): Response
	{
		// On récupère l'ensemble des applications
		$applications = $appliRepo->findAll();
		$demande = $demandRepo->findAssociated((int)$currentUser->getId(), (int)$service->getId());

		// Si il n'y a encore pas de demande associée à ce service pour cet agent dans la BDD, on en créé une
		if ($demande === false) {
			$demande = new Demande();
		}

		// Si formulaire envoyé, on le traite
		if (!empty($_POST)) {
			foreach ($_POST as $id => $code) {
				//$demande-add
			}

		}

		return $this->render('user/demande/edit.html.twig', [
			'service' => $service,
			'applications' => $applications 
		]);
	}
}