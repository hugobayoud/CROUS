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
		// On crée un ArrayCollection de tous les users
		$users = new Users();
		// On récupère tous les users validés dans BDD (à l'exception de l'user connecté, il ne peut pas se modifier lui-même)
		$allUsers = $userRepo->findAllValidated($currentUser->getId());

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
	public function usersListDSI(UserRepository $userRepo, DsiRepository $dsiRepo, Request $request, UserInterface $currentUser)
	{
		$dsis = new Dsis();
		$originalDsis = new Dsis();
		$user = $userRepo->findOneBy(['id' => 41]);
		$allDsisForOneUser = $dsiRepo->findBy(['user' => 41]);

		foreach ($allDsisForOneUser as $dsi) {
			$dsis->getDsis()->add($dsi);
		}

		// Sauvegarder le tableau de DSI d'un user avant qu'il y ait modification
		foreach ($dsis->getDsis() as $aDsi) {
			$originalDsis->addDsi($aDsi);
		}

		// On crée un form de ce tableau
		$form = $this->createForm(DsisType::class, $dsis);
		$form->handleRequest($request);

		// On persiste en BDD
        if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();

			// Les dates qui sont dans $dsis et dans $originalDsis n'ont pas subit de modification, on y touche pas,
			// Les dates qui ne sont pas dans $dsis mais qui sont dans $originalDsis sont des dates supprimées, elles doivent être enlevées en base,
			// Les dates qui sont dans $dsis mais pas dans $originalDsis sont les nouvelles dates à enregistrer en base.

			// SUPPRESION EN BASE
			foreach ($originalDsis->getDsis() as $dsi) {
				//Si le form enregistré a enlevé une date, alors elle est enlevé de la BDD
				if (false === $dsis->getDsis()->contains($dsi)) {
					// On supprime de la BDD
					$em->remove($dsi);
					$em->flush();
				}
			}

			// AJOUT EN BASE
			foreach ($dsis->getDsis() as $dsi) {
				if (false === $originalDsis->getDsis()->contains($dsi)) {
					// On associe cette période au bon agent
					$dsi->setUser($user);
					$em->persist($dsi);
					$em->flush();
				}
			}

			// On notifie que tout s'est bien passé
			$this->addFlash('message', "Modifications enregistrées avec succès");
			return $this->redirectToRoute('admin.utilisateurs.dsi');
		}
		
		return $this->render('admin/users_dsi.html.twig', [
			'user' => $user,
			'form' => $form->createView()
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
