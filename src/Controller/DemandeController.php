<?php
namespace App\Controller;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DemandeController extends AbstractController
{
	/**
	 * @Route("/demande", name="demande.create")
	 * @return Response
	 */
	public function create(): Response
	{
		return $this->render('demande/create.html.twig');
	}
}