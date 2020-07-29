<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Users;
use App\Form\UsersType;
use App\Repository\UserRepository;
use App\Repository\DemandeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/dsi", name="dsi.")
 * 
 * @IsGranted("ROLE_DSI")
 */
class DSIController extends AbstractController
{
    /**
	 * Page d'accueil pour la DSI. Propose toutes les fonctionnalités possibles en tant que DSI
     * @Route("/", name="home")
     */
    public function index(DemandeRepository $demandRepo, UserRepository $userRepo)
    {
		// Nombre de nouveaux comptes à valider
		$countNewAccounts = $userRepo->countNewAccounts();
		// Nombre de demandes en cours en attente de validation par un DSI
		$countDemands1 = $demandRepo->countDemandsState(1);

        return $this->render('dsi/index.html.twig', [
			'countNewAccounts' => $countNewAccounts,
			'countDemands1' => $countDemands1
		]);
	}
	
	/**
	 * Liste tous les utilisateurs inscris en BDD qui ne sont pas encore validés
	 * @Route("/gestion/nouveaux-comptes", name="gestion-nouveaux-comptes")
	 */
	public function administerNewAccounts(UserRepository $userRepo)
	{
		return $this->render("dsi/gestion-nouveaux-comptes/index.html.twig", [
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
			return $this->redirectToRoute('dsi.gestion-utilisateurs');
        }

        return $this->render('dsi/gestion-utilisateurs/index.html.twig', [
			'form' => $form->createView(),
			'users' => $allUsers
        ]);
	}

	/**
	 * Liste de tous les utilisateurs validés pour modification de leurs rôles de DSI au sein du CROUS
	 * @Route("/consultation/dsis", name="consultation-dsis")
	 */
	public function administerDSIS(UserRepository $userRepo, Request $request)
	{
		// On récupère tous les users
		$users = $userRepo->findAllValidatedByNameASC();

		return $this->render('dsi/consultation-dsis/index.html.twig', [
			'users' => $users,
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
		$target = 'dsi.gestion';
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
		
		return $this->render('dsi/consultation-droits/index.html.twig', [
			'users' => $users
		]);
	}
}
