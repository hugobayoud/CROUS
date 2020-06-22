<?php
namespace App\Controller;

use App\Repository\ServiceRepository;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DemandeController extends AbstractController
{
	/**
	 * @Route("/demande", name="demandes")
	 * @return Response
	 */
	public function create(UserInterface $currentUser, ServiceRepository $serviceRepo): Response
	{
		$services = $serviceRepo->findAllByUserId($currentUser->getId());
		return $this->render('demande/demandes.html.twig', [
			'services' => $services
		]);
	}
}