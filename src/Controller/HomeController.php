<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
	/**
	 * Page home de la plate-forme, si l'agent se connecte avec un rôle admin, il est redirigé vers la home admin (/admin)
	 * Si l'utilisateur n'est pas encore connecté, il est rédirigé vers la page de connexion.
	 * 
	 * @Route("/", name="home")
	 * @return Response
	 */
	public function index(): Response
	{
		if ($this->getUser() !== NULL) {
			// Si l'user est un admin ou un dsi alors il est redirigé vers home admin (/admin)
			if (in_array('ROLE_ADMIN', $this->getUser()->getRoles()) || in_array('ROLE_DSI', $this->getUser()->getRoles())) {
				return $this->redirectToRoute('admin.home');
			}
		} else {
			// Si l'user n'est pas encore connecté, il est redirigé vers la page de connexion
			return $this->redirectToRoute('security.login');
		}

		return $this->render('user/home/index.html.twig');
	}
}