<?php

namespace App\Controller;

use App\Entity\Dsis;
use App\Form\DsisType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin", name="admin.")
 * 
 * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends AbstractController
{
	/**
	 * Page d'accueil pour un administrateur. Propose toutes les fonctionnalités possibles en tant que administrateur
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig');
	}

	/**
	 * Liste de tous les utilisateurs validés pour modification de leurs rôles de DSI au sein du CROUS
	 * @Route("/gestion/dsis", name="gestion-dsis")
	 */
	public function administerDSIS(UserRepository $userRepo, Request $request)
	{
		$user = $this->getUser();
		// On récupère tous les users
		$users = $userRepo->findAllValidated($user->getId());

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
}