<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Users;
use App\Entity\Service;
use App\Form\UsersType;
use App\Entity\Validateur;
use App\Form\EditUserDSIType;
use App\Form\EditUserServiceType;
use App\Repository\UserRepository;
use App\Repository\ValidateurRepository;
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
     * @Route("/", name="index")
     */
    public function index(UserRepository $userRepo)
    {
		// On récupère le nombre de demandes de création de compte à valider

		// On récupère le nombre de demandes validées par un valideur qu'il faut traiter
		// Fait intervenir la table DEMANDE

        return $this->render('admin/index.html.twig', [
			'controller_name' => 'AdminController',
			'nbNewAccount' => $userRepo->countNewAccount()
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
		$users = new Users();
		$allUsers = $userRepo->findAllValidated($currentUser->getId());

		foreach ($allUsers as $user) {
			$users->getUsers()->add($user);
		}

		$form = $this->createForm(UsersType::class, $users);
		$form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			foreach ($users->getUsers() as $user) {
				$em->persist($user);
				$em->flush();
			}

			$this->addFlash('message', "Modifications enregistrées avec succès");
			return $this->redirectToRoute('admin.utilisateurs');
        }

        return $this->render('admin/users.html.twig', [
			'form' => $form->createView(),
			'users' => $allUsers
        ]);
	}

	/**
	 * Liste tous les utilisateurs validés pour modification de leurs rôles de valideur pour chaque service
	 * 
	 * @Route("/utilisateurs/valideurs", name="utilisateurs.valideurs")
	 */
	public function usersListValidators(UserRepository $userRepo, Request $request, UserInterface $currentUser)
	{
		return $this->render("admin/users_validators.html.twig");
	}

	/**
	 * Liste de tous les utilisateurs validés pour modification de leurs rôles de DSI au sein du CROUS
	 * 
	 * @Route("/utilisateurs/dsi", name="utilisateurs.dsi")
	 */
	public function usersListDSI(UserRepository $userRepo, Request $request, UserInterface $currentUser)
	{
		return $this->render('admin/users_dsi.html.twig');
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
