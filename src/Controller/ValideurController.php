<?php

namespace App\Controller;

use App\Entity\Dsis;
use App\Entity\User;
use App\Entity\Users;
use App\Form\DsisType;
use App\Entity\Service;
use App\Form\UsersType;
use App\Entity\Validateur;
use App\Form\EditUserDSIType;
use App\Form\EditUserServiceType;
use App\Repository\DsiRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Repository\ValidateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/valideurs", name="valideurs.")
 */
class ValideurController extends AbstractController
{
	/**
     * @Route("/", name="home")
	 * 
	 * On affiche tous les services de l'user et il choisit dans quel service il souhaite faire une modification de valideur
	 * Trois types de personnes :
	 * 		- C'est un admin ou un dsi en cours => il peut aller dans tous les services et nommer n'importe quel agent de ce service valideur
	 * 		- C'est un agent mais il a le pouvoir valideur en cours dans ce service alors il peut promouvoir qui il souhaite valideur
	 * 		- C'est un agent et il n'a pas de pouvoir valideur mais il peut regarder qui est valideur sans rien pouvoir modifier
    */
	public function servicesList(ServiceRepository $serviceRepo)
	{
		if ($this->getUser() !== NULL) {
			if (in_array('ROLE_ADMIN', $this->getUser()->getRoles()) || $this->getUser()->verifyCurrentDsi()) {
				// Si l'user est un admin ou un dsi en cours alors il a accès à tous les services, qu'il soit valideur ou non
				$services = $serviceRepo->findAll();
				//...
			} else {
				// Sinon, on ne lui affiche que les services dont il fait partie (qu'il soit valideur ou non)
				$services = $serviceRepo->findAllByUserId($this->getUser()->getId());
				//...
			}
		}

		return $this->render("validators/home/index.html.twig", [
			'services' => $services,
		]);
	}

	/**
     * @Route("/{id}", name="service")
    */
	public function validatorsServiceList(UserRepository $userRepo, Service $service)
	{
		$users = $userRepo->findAllByServiceId($service->getId());
		return $this->render("validators/service.html.twig", [
			'users' => $users,
			'service' => $service,
		]);
	}

}




















