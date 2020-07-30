<?php

namespace App\Controller;

use App\Entity\Service;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/agent", name="agent.")
 */
class AgentController extends AbstractController
{
	/**
	 * Page d'accueil de l'agent
     * @Route("/", name="home")
    */
	public function index()
	{
		return $this->render("agent/index.html.twig");
	}

	/**
     * @Route("/connaitre-valideurs/{id}", name="connaitre-valideurs")
    */
	public function showValidators(Service $service)
	{
		return $this->render('agent/connaitre-valideurs/service_show.html.twig', [
			'service' => $service
		]);
	}
}