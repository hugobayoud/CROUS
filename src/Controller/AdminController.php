<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditUserRoleType;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin", name="admin.")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
	}
	
	/**
	 * Liste tous les utilisateurs inscris en BDD qui ne sont pas encore validés
	 * 
	 * @Route("/utilisateurs_validation", name="utilisateurs.validation")
	 */
	public function usersValidationList(UserRepository $users)
	{
		return $this->render("admin/users_validation.html.twig", [
			'users' => $users->findAllNotValidated()
		]);
	}

	/**
	 * Liste tous les utilisateurs validés pour modification de leur roles
	 * 
	 * @Route("/utilisateurs_roles", name="utilisateurs.roles")
	 */
	public function usersRolesList(UserRepository $users)
	{
		return $this->render("admin/users_roles.html.twig", [
			'users' => $users->findAllValidated()
		]);
	}

	/**
	 * Modifier le rôle d'un user
	 * 
	 * @Route("/utilisateurs_roles/modifier/{id}", name="utilisateurs.roles.modifier")
	 */
	public function editUser(User $user, Request $request)
	{
		$form = $this->createForm(EditUserRoleType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();

			$this->addFlash('message', "Modifications enregistrées avec succès");

			return $this->redirectToRoute('admin.utilisateurs.roles');
		}

		return $this->render('admin/editUser.html.twig', [
			'form' => $form->createView()
		]);
	}

	/**
	 * @Route("/admin/utilisateur/{id}", name="utilisateur.delete", methods="DELETE")
	 * @param int $id : id de l'utilisateur a refuser et enlever de la BDD
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
		
		return $this->redirectToRoute('admin.utilisateurs.validation');
	}
}
