<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Service;
use App\Entity\Valideurs;
use App\Form\SearchForm;
use App\Form\ValideursType;
use App\Repository\UserRepository;
use App\Repository\ServiceRepository;
use App\Repository\ValideurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/valideur", name="valideur.")
 */
class ValideurController extends AbstractController
{
	/**
	 * Home pour un valideur (quelqu'un qui est valideur d'un ou plusieurs service(s))
	 * @Route("/", name="home")
	 */
	public function index()
	{
		if (!$this->getUser()->isAValidator()) {
			return $this->redirectToRoute('login');
		}

		return $this->render('valideur/index.html.twig');
	}

	/**
     * @Route("/gestion/valideurs", name="gestion-valideurs.home")
	 * 
	 * On affiche tous les services de l'user et il choisit dans quel service il souhaite faire une modification de valideur
	 * Trois types de personnes :
	 * 		- C'est un admin ou un dsi en cours => il peut aller dans tous les services et nommer n'importe quel agent de ce service valideur
	 * 		- C'est un agent mais il a le pouvoir valideur en cours dans ce service alors il peut promouvoir qui il souhaite valideur
	 * 		- C'est un agent et il n'a pas de pouvoir valideur mais il peut regarder qui est valideur sans rien pouvoir modifier
    */
	public function servicesList(ServiceRepository $serviceRepo, Request $request)
	{
		$user = $this->getUser();

		$data = new SearchData();
		$form = $this->createForm(SearchForm::class, $data);
		$form->handleRequest($request);

		if ($user->isAdmin() || $user->isDSI()) {
			// Si l'user est un admin ou un dsi en cours alors il a accès à tous les services, qu'il soit valideur ou non
			$services = $serviceRepo->findSearch($data);
		} else {
			// Sinon, on ne lui affiche que les services dont il fait partie (qu'il soit valideur ou non)
			$services = $user->getServices();
		}

		return $this->render("valideur/gestion-valideurs/index.html.twig", [
			'services' => $services,
			'form' => $form->createView()
		]);
	}

	/**
     * @Route("/gestion/valideurs/{id}", name="gestion-valideurs.service")
    */
	public function validatorsServiceList(Request $request, Service $service)
	{
		$currentUser = $this->getUser();

		if ($currentUser->isAdmin() || $currentUser->isDSI() || $currentUser->isValidator($service->getId())) {
			// On récupère les agents de ce service (dont leur compte ont été validé)
			$users = $service->getValidatedUsers();
			$forms = [];

			foreach ($users as $user) {
				$valideurs = new Valideurs();
				foreach ($user->getValideurs() as $valideur) {
					// Si cette période correspond bien au service actuel
					if ($valideur->getService()->getId() === $service->getId()) {
						$valideurs->getValideurs()->add($valideur);
					}
				}
				// On crée un form du tableau de DSIs d'un user pour chaque user
				$forms[] = $this->createForm(ValideursType::class, $valideurs);
			}

			// Si le formulaire est envoyé, on récupère l'id correspondant à l'agent et les modifications à faire en BDD
			if (isset($_POST["custId"])) {
				$index = $_POST["custId"];
				$form = $forms[$index]->handleRequest($request);

				// Si le form est soumis et valide
				if ($form->isSubmitted() && $form->isValid()) {
					$em = $this->getDoctrine()->getManager();

					// SUPPRESION EN BASE DES ANCIENNES PERIODES
					foreach ($users[$index]->getValideurs() as $valideur) {
						if ($valideur->getService()->getId() === $service->getId()) {
							$em->remove($valideur);
							$em->flush();
						}
					}
		
					// AJOUT EN BASE
					foreach ($form->getData()->getValideurs() as $valideur) {
						$valideur->setUser($users[$index]);
						$valideur->setService($service);
						$valideur->getDateFin()->setTime(23, 59, 59);
						$em->persist($valideur);
						$em->flush();
					}
		
					// On notifie que tout s'est bien passé
					$this->addFlash('message', "Modifications enregistrées avec succès pour l'agent " . $users[$index]->getPrenom() . " " . $users[$index]->getNom() . ".");
					return $this->redirectToRoute('valideur.gestion-valideurs.service', [
						'id' => $service->getId()
					]);
				}
			}

			$renderedForms = [];
			foreach ($forms as $form) {
				$renderedForms[] = $form->createView();
			}

			return $this->render('valideur/gestion-valideurs/service.html.twig', [
				'service' => $service,
				'users' => $users,
				'forms' => $renderedForms, 
			]);
		} else {
			$this->addFlash('warning', "acces refusé");
			return $this->redirectToRoute('valideurs.home');
		}
	}

	/**
	 * Validations des demandes. Affichage de tous les services dont l'user est valideur. Ne doit pasaccèder à cette page s'il est valideur dans aucun service
	 * @Route("/validation/demandes", name="validation-demandes.home")
	 */
	public function validateDemandsHome()
	{
		$user = $this->getUser();

		if ($user->isAValidator()) {
			$services = $user->getServicesWhereValidator();
			return $this->render('valideur/validation-demandes/index.html.twig', [
				'services' => $services
			]);

		} else {
			$this->addFlash('warning', "ACCES REFUSE : Vous n'êtes valideur d'aucun service.");
			return $this->redirectToRoute('home');
		}
	}

	/**
     * @Route("/validation/demandes/{id}", name="validation-demandes.service")
    */
	public function validateDemandsService(Request $request, Service $service)
	{
		$currentUser = $this->getUser();

		if ($currentUser->isValidator($service->getId())) {
			// On récupère les demandes de ce service
			//$demandes = $service->getDemandeDeCeService();

			return $this->render('valideur/validation-demandes/service.html.twig', [
				'service' => $service
			]);
		} else {
			$this->addFlash('warning', "acces refusé, vous n'êtes pas valideur de ce service");
			return $this->redirectToRoute('valideur.validation-demandes.home');
		}
	}
}