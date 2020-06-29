<?php

namespace App\Controller;

use App\Entity\Dsis;
use App\Entity\User;
use App\Entity\Users;
use App\Form\DsisType;
use App\Form\UsersType;
use App\Repository\DemandeRepository;
use App\Repository\DsiRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin", name="admin.")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(UserRepository $userRepo, DemandeRepository $demandRepo)
    {
        return $this->render('admin//home/index.html.twig', [
			'controller_name' => 'AdminController',
			'nbNewAccount' => $userRepo->countNewAccount(),
			'nbDemandsState2' => $demandRepo->countDemandState(2)
        ]);
	}
	
	/**
	 * Liste tous les utilisateurs inscris en BDD qui ne sont pas encore validés
	 * 
	 * @Route("/utilisateurs_pour_validation", name="utilisateurs.validation")
	 */
	public function usersToValidationList(UserRepository $users)
	{
		return $this->render("admin/users_validation.html.twig", [
			'users' => $users->findAllNotValidated()
		]);
	}

	/**
	 * Liste tous les utilisateurs validés pour modification de leurs status
	 * 
	 * @param UserInterface $currentUser
	 * 
	 * @Route("/utilisateurs", name="utilisateurs")
	 */
	public function usersList(UserRepository $userRepo, Request $request, UserInterface $currentUser)
	{
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
			return $this->redirectToRoute('admin.utilisateurs');
        }

        return $this->render('admin/users.html.twig', [
			'form' => $form->createView(),
			'users' => $allUsers
        ]);
	}

	/**
	 * Liste de tous les utilisateurs validés pour modification de leurs rôles de DSI au sein du CROUS
	 * 
	 * @Route("/utilisateurs/dsi", name="utilisateurs.dsi")
	 */
	public function usersListDSI(UserRepository $userRepo, DsiRepository $dsiRepo, Request $request, UserInterface $currentUser)
	{
		// Tableau de User
		$users = $userRepo->findAllValidatedByNameASC($currentUser->getId());

		$forms = [];
		foreach ($users as $user) {
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
			// On handleRequest() sur le bon formulaire
			$form = $forms[$index]->handleRequest($request);

			// Si le form est soumis et valide
			if ($form->isSubmitted() && $form->isValid()) {
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
				return $this->redirectToRoute('admin.utilisateurs.dsi');
			}
		}

		$renderedForms = [];
		foreach ($forms as $form) {
			$renderedForms[] = $form->createView();
		}

		return $this->render('admin/users_dsi.html.twig', [
			'users' => $users,
			'forms' => $renderedForms, 
		]);
	}


	/**
	 * @Route("/utilisateur/{id}/delete", name="utilisateur.delete", methods="DELETE")
	 * @param int $id : id de l'user en demande de validation a refuser ou l'user a enlever de la BDD
	 * @return Response
	 */
	public function delete(int $id, Request $request): Response
	{
		$user = $this->getDoctrine()->getManager()->find(User::class, $id);
		if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->get('_token'))) {
			$em = $this->getDoctrine()->getManager();
			$em->remove($user);
			$em->flush();
			$this->addFlash('success', 'Suppression correctement effectuée');
		}


		// On cherche la bonne redirection car le bouton suppr est utilisé à plusieurs endroits
		$url = $_SERVER['HTTP_REFERER'];
		$target = 'admin.utilisateurs';

		$parts = explode('/', $url);
		if (end($parts) === 'utilisateurs_pour_validation') {
			$target .= '.validation';
		}
		
		return $this->redirectToRoute($target);
	}
}
