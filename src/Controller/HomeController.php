<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
	/**
	 * Si l'utilisateur n'est pas encore connecté, il est rédirigé vers la page de connexion.
	 * 
	 * @Route("/", name="home")
	 * @return Response
	 */
	public function index(): Response
	{
		$user = $this->getUser();

		if (is_null($user)) {
			return $this->redirectToRoute('security.login');
		} else {
			if ($user->isAdmin() || $user->isDSI()) {
				return $this->redirectToRoute('admin.home');
			} else if ($user->isAValidator()) {
				return $this->redirectToRoute('valideur.home');
			} else {
				return $this->redirectToRoute('agent.home');
			}
		}

		return $this->render('index.html.twig');
	}
}