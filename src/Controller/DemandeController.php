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
	public function demandForOneService(UserInterface $currentUser, Service $service, ApplicationRepository $appliRepo): Response
	{
		$applications = $appliRepo->findAll();

		return $this->render('user/demande/edit.html.twig', [
			'applications' => $applications,
			'service' => $service
		]);
	}
}