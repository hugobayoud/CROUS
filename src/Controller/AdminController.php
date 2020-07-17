<?php

namespace App\Controller;

use App\Entity\Dsis;
use App\Entity\User;
use App\Entity\Users;
use App\Form\DsisType;
use App\Form\UsersType;
use App\Repository\DemandeRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin", name="admin.")
 */
class AdminController extends AbstractController
{
    /**
	 * Page d'accueil de l'administration et de la DSI. Propose toutes les fonctionnalités possibles en tant qu'admin
     * @Route("/", name="home")
     */
    public function index(DemandeRepository $demandRepo, UserRepository $userRepo)
    {
		// Nombre de nouveaux comptes à valider
		$newAccounts = $userRepo->countNewAccounts();
		// Nombre de demandes en cours en attente de validation par un DSI
		$demandsState1 = $demandRepo->countDemandsState(1);

        return $this->render('admin/index.html.twig', [
			'newAccounts' => $newAccounts,
			'demandsState1' => $demandsState1
		]);
	}
	
	/**
	 * Liste tous les utilisateurs inscris en BDD qui ne sont pas encore validés
	 * @Route("/gestion/nouveaux-comptes", name="gestion-nouveaux-comptes")
	 */
	public function administerNewAccounts(UserRepository $userRepo)
	{
		return $this->render("admin/gestion-nouveaux-comptes/index.html.twig", [
			'users' => $userRepo->findAllNotValidated()
		]);
	}

	/**
	 * Liste tous les utilisateurs validés pour modification de leurs status
	 * @Route("/gestion/utilisateurs", name="gestion-utilisateurs")
	 */
	public function administerUsers(UserRepository $userRepo, Request $request)
	{
		$currentUser = $this->getUser();
		// On crée un ArrayCollection de tous les users
		$users = new Users();
		// On récupère tous les users validés dans BDD (à l'exception de l'user connecté, il ne peut pas se modifier lui-même)
		$allUsers = $userRepo->findAllValidatedByNameASC($currentUser->getId());

		// On ajoute les users au tableau
		foreach ($allUsers as $user) {
			$users->getUsers()->add($user);
		}

		// On crée un form de ce tableau
		$form = $this->createForm(UsersType::class, $users);
		$form->handleRequest($request);

		// On persiste en BDD
        if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			foreach ($users->getUsers() as $user) {
				$em->persist($user);
				$em->flush();
			}

			// On notifie que tout s'est bien passé
			$this->addFlash('message', "Modifications enregistrées avec succès");
			return $this->redirectToRoute('admin.gestion-utilisateurs');
        }

        return $this->render('admin/gestion-utilisateurs/index.html.twig', [
			'form' => $form->createView(),
			'users' => $allUsers
        ]);
	}

	/**
	 * Liste de tous les utilisateurs validés pour modification de leurs rôles de DSI au sein du CROUS
	 * @Route("/gestion/dsis", name="gestion-dsis")
	 */
	public function administerDSIS(UserRepository $userRepo, Request $request)
	{
		$user = $this->getUser();
		// On récupère tous les users
		$users = $userRepo->findAllValidatedByNameASC($user->getId());

		$forms = [];
		foreach ($users as $user) {
			// On crée un tableau qui recense toutes les périodes DSI de l'agent
			$dsis = new Dsis();
			foreach ($user->getDsis() as $dsi) {
				// Dsis d'un user qui sera utile pour les formulaires
				$dsis->getDsis()->add($dsi);
			}

			// On crée un form du tableau de DSIs d'un user pour chaque user
			$forms[] = $this->createForm(DsisType::class, $dsis);
		}

		// Si le formulaire est envoyé, on récupère l'id correspondant à l'agent et les modifications à faire en BDD
		if (isset($_POST["custId"])) {
			$index = $_POST["custId"];
			// On handleRequest() sur le bon formulaire qui a été modifié
			$form = $forms[$index]->handleRequest($request);

			// Si le form est soumis et valide
			if ($form->isSubmitted() && $form->isValid()) {
				// On récupère l'entity manager
				$em = $this->getDoctrine()->getManager();
								
				// SUPPRESION EN BASE DES ANCIENNES PERIODES
				foreach ($users[$index]->getDsis() as $dsi) {
					$em->remove($dsi);
					$em->flush();
				}
	
				// AJOUT EN BASE DES NOUVELLES PERIODES
				foreach ($form->getData()->getDsis() as $dsi) {
					// On associe cette période au bon agent
					$dsi->setUser($users[$index]);
					$dsi->getDateFin()->setTime(23, 59, 59);
					$em->persist($dsi);
					$em->flush();
				}
	
				// On notifie que tout s'est bien passé
				$this->addFlash('message', "Modifications enregistrées avec succès pour l'agent " . $users[$index]->getPrenom() . " " . $users[$index]->getNom() . ".");
				// On rédirige vers la page en cours
				return $this->redirectToRoute('admin.gestion-dsis');
			}
		}

		// On fait le rendu de chaque formulaire
		$renderedForms = [];
		foreach ($forms as $form) {
			$renderedForms[] = $form->createView();
		}

		return $this->render('admin/gestion-dsis/index.html.twig', [
			'users' => $users,
			'forms' => $renderedForms, 
		]);
	}


	/**
	 * Supprimer une demande de nouveau compte ou supprimer un user de la BDD
	 * @Route("/utilisateur/{id}/supprimer", name="supprimer-utilisateur", methods="DELETE")
	 * @return Response
	 */
	public function deleteUser(int $userId, Request $request): Response
	{
		$user = $this->getDoctrine()->getManager()->find(User::class, $userId);

		if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->get('_token'))) {
			$em = $this->getDoctrine()->getManager();
			//$em->remove($user);
			//$em->flush();
			$this->addFlash('message', 'Suppression correctement effectuée');
		}

		// On cherche la bonne redirection car le bouton suppr est utilisé à plusieurs endroits
		$url = $_SERVER['HTTP_REFERER'];
		$target = 'admin.gestion';
		$parts = explode('/', $url);

		if (end($parts) === 'nouveaux-comptes') {
			$target .= '-nouveaux-comptes';
		} else {
			$target .= '-utilisateurs';
		}
		
		return $this->redirectToRoute($target);
	}

	/**
	 * Consultation des droits effetifs en cours pour tout le centre Clermont-Auvergne
	 * @Route("/consultation-droits", name="consultation-droits.home")
	 */
	public function consultAccess(UserRepository $userRepo)
	{
		$users = $userRepo->findAll();
		
		return $this->render('admin/consultation-droits/index.html.twig', [
			'users' => $users
		]);
	}
}
