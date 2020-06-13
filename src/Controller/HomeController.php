<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
	/**
	 * @Route("/", name="home")
	 * @return Response
	 */
	public function index(): Response
	{
		if ($this->getUser() !== NULL) {
			// Si l'user est un admin ou un dsi alors il est automatiquement redirigé vers la page d'accueil d'un admin
			if (in_array('ROLE_ADMIN', $this->getUser()->getRoles()) || in_array('ROLE_DSI', $this->getUser()->getRoles())) {
				return $this->redirectToRoute('admin.index');
			}
		}

		return $this->render('pages/home.html.twig');
	}
}