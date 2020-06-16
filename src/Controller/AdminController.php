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
	 * Liste tous les utilisateurs validés pour modification de leurs roles (DSI et Valideur de chaque service)
	 * 
	 * @Route("/utilisateurs/roles", name="utilisateurs.roles")
	 */
	public function usersListRoles(UserRepository $users)
	{
		return $this->render("admin/users_roles.html.twig", [
			'users' => $users->findAllValidated()
		]);
	}

	/**
	 * Modifier le rôle DSI d'un user
	 * 
	 * @Route("/utilisateur/{id}/dsi/modifier", name="utilisateur.dsi.modifier")
	 */
	public function editUserDSI(User $user, Request $request)
	{
		$formDSI = $this->createForm(EditUserDSIType::class, $user);
		$formDSI->handleRequest($request);

		if ($formDSI->isSubmitted() && $formDSI->isValid()) {
			if ($formDSI->get('dsi')->getData() === TRUE) {
				$user->addRole("USER_DSI");
			} else {
				$user->removeRole("USER_DSI");
			}
			
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();

			$this->addFlash('message', "Modifications enregistrées avec succès");

			return $this->redirectToRoute('admin.utilisateurs');
		}

		return $this->render('admin/edit_user_dsi.html.twig', [
			'form' => $formDSI->createView(),
			'user' => $user
		]);
	}

	/**
	 * Modifier un rôle possédé par un user
	 * 
	 * @Route("/utilisateur/{id_user}/service/{id_service}/modifier", name="utilisateur.service.modifier")
	 */
	public function editUserService($id_user, $id_service, Request $request, ValidateurRepository $validateurRepo)
	{
		$validateur = $validateurRepo->findOneByIdUserAndService($id_user, $id_service);

		// Si il y a une ligne dans la table VALIDATEUR pour ce user, ce service alors on met les données dans le form
		if ($validateur !== NULL) {
			$form = $this->createForm(EditUserServiceType::class, $validateur);
			$form->get('valide')->setData(true);
			
		// Sinon on créé un form vierge
		} else {
			$form = $this->createForm(EditUserServiceType::class);
		}

		$form->handleRequest($request);
		$em = $this->getDoctrine()->getManager();

		// On récupère le user et le service correspondants pour les envoyer à la vue
		$user = $em->find(User::class, $id_user);
		$service = $em->find(Service::class, $id_service);

		if ($form->isSubmitted() && $form->isValid()) {

			// On recupère le validateur s'il existe deja dans la table VALIDATEUR
			$validateur = $validateurRepo->findOneByIdUserAndService($id_user, $id_service);
			
			// Si il y a une ligne pour ce service pour ce user dans la table on le supprime.
			if ($validateur !== NULL) {
				$em->remove($validateur);
			}

			// Si l'input 'valide' du form est TRUE alors on doit enregsitrer de nouvelles infos.
			if ($form->get('valide')->getData() === TRUE) {
				$validateur = new Validateur();
				$validateur
						->setIdUser($user)
						->setIdService($service)
						->setDateDeb($form->get('date_deb')->getData())
						->setDateFin($form->get('date_fin')->getData());
				
				$em->persist($validateur);
			}
			
			$em->flush();

			$this->addFlash('message', "Modifications enregistrées avec succès");

			return $this->redirectToRoute('admin.utilisateurs');
		}


		return $this->render('admin/edit_user_service.html.twig', [
			'form' => $form->createView(),
			'user' => $user,
			'service' => $service
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
