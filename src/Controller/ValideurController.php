<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\Valideurs;
use App\Form\ValideursType;
use App\Repository\UserRepository;
use App\Repository\ServiceRepository;
use App\Repository\ValideurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

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
	public function validatorsServiceList(ValideurRepository $valideurRepo, Request $request, Service $service)
	{
		// $users = $userRepo->findAllByServiceId($service->getId());
		// return $this->render("validators/service.html.twig", [
		// 	'users' => $users,
		// 	'service' => $service,
		// ]);

		//----------------------------------------------------------------------------------------------



		// Tableau de User
		//$users = $userRepo->findAllByServiceId($service->getId());
		$users = $service->getUsers();

		// Tableau de tableau de DSIs pour chaque user
		$allDsis = [];
		$allOriginalDsis = [];
		$forms = [];

		foreach ($users as $user) {
			// On récupère les Dsis de chaque user un par un
			// $allDsisForOneUser = $dsiRepo->findBy(['user' => $user->getId()]);
			$allDsisForOneUser = $valideurRepo->findByOrderByDateDeb(['user' => $user->getId()]);
			
			$dsis = new Valideurs();
			$originalDsis = new Valideurs();
			foreach ($allDsisForOneUser as $dsi) {
				// Dsis d'un user qui sera utile pour les formulaires
				$dsis->getValideurs()->add($dsi);
				// Dsis d'un user stockés afin de comparer les Dsis d'un user avant et après modificiation des formulaires par l'admin
				$originalDsis->getValideurs()->add($dsi);
			}

			$allDsis[] = $dsis;
			$allOriginalDsis[] = $originalDsis;

			// On crée un form du tableau de DSIs d'un user pour chaque user
			$forms[] = $this->createForm(ValideursType::class, $dsis);
		}

		// Si le formulaire est envoyé, on récupère l'id correspondant à l'agent et les modifications à faire en BDD
		if (isset($_POST["custId"])) {
			$index = $_POST["custId"];
			// On handleRequest() sur le bon formulaire
			$form = $forms[$index]->handleRequest($request);

			// Si le form est soumis et valide
			if ($form->isSubmitted() && $form->isValid()) {
				$em = $this->getDoctrine()->getManager();
								
				// Les dates qui sont dans $dsis et dans $originalDsis n'ont pas subit de modification, on y touche pas,
				// Les dates qui ne sont pas dans $dsis mais qui sont dans $originalDsis sont des dates supprimées, elles doivent être enlevées en BDD,
				// Les dates qui sont dans $dsis mais pas dans $originalDsis sont les nouvelles dates à enregistrer en BDD.
	
				// SUPPRESION EN BASE
				foreach ($allOriginalDsis[$index]->getValideurs() as $dsi) {
					//Si le form a enlevé une date, alors elle est enlevée de la BDD
					if (false === $allDsis[$index]->getValideurs()->contains($dsi)) {
						// On supprime de la BDD
						$em->remove($dsi);
						$em->flush();
					}
				}
	
				// AJOUT EN BASE
				foreach ($allDsis[$index]->getValideurs() as $dsi) {
					if (false === $allOriginalDsis[$index]->getValideurs()->contains($dsi)) {
						// On associe cette période au bon agent
						$dsi->setUser($users[$index]);
						$dsi->setService($service);
						$em->persist($dsi);
						$em->flush();
					}
				}
	
				// On notifie que tout s'est bien passé
				$this->addFlash('message', "Modifications enregistrées avec succès pour l'agent " . $users[$index]->getPrenom() . " " . $users[$index]->getNom() . ".");
				return $this->redirectToRoute('valideurs.service', [
					'id' => $service->getId()
				]);
			}
		}

		$tmpForms = [];
		foreach ($forms as $form) {
			$tmpForms[] = $form->createView();
		}

		return $this->render('validators/service.html.twig', [
			'service' => $service,
			'users' => $users,
			'forms' => $tmpForms, 
		]);
	}

}




















