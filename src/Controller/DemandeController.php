<?php
namespace App\Controller;

use DateTime;
use Exception;
use Twig\Environment;
use App\Entity\Demande;
use App\Entity\Service;
use App\Helper\DateHelper;
use App\Entity\ApplicationDemande;
use App\Repository\UserRepository;
use App\Repository\DemandeRepository;
use App\Repository\ServiceRepository;
use App\Repository\ApplicationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DemandeController extends AbstractController
{
	/**
	 * Montre tous les services d'un agent pour lequel il peut faire une demande
	 * 
	 * @Route("/user/demandes", name="user.demandes")
	 * @return Response
	 */
	public function servicesList(UserInterface $currentUser): Response
	{
		$services = $currentUser->getServices();

		return $this->render('user/demande/demandes.html.twig', [
			'services' => $services
		]);
	}

	/**
	 * @Route("/user/demande/{id}", name="user.demande.edit")
	 * @return Response
	 */
	public function demandForOneService(UserInterface $currentUser, Service $service, ApplicationRepository $appliRepo, DemandeRepository $demandRepo): Response
	{
		$applications = $appliRepo->findAll();

		if (!empty($_POST)) {
			$demande = $demandRepo->findOneBy([
				'user' => $currentUser,
				'service' => $service
			]);
			
			$em = $this->getDoctrine()->getManager();

			// S'il n'existe pas encore de demande pour ce service pour ce user en base
			if (is_null($demande)) {
				$demande = new Demande();
				$demande->setUser($currentUser);
				$demande->setService($service);
			} else {
				// Sinon, on supprime les anciennes demandes d'applications pour ce service pour cet agent dans application_demande
				$rows = $demande->getApplications();
				foreach ($rows as $appli_demande) {
					$em->remove($appli_demande);
					$em->flush();
				}
			}

			$demande->setEtat(0);
			$demande->setCreatedAt(new DateTime('now'));

			$em->persist($demande);
			$em->flush();
			
			// On enregistre chaque application demandée dans la table application_demande
			foreach ($_POST as $id => $code) {
				$ad = new ApplicationDemande();
				$ad->setApplication($appliRepo->findOneBy(['id' => $id]));
				$ad->setDemande($demande);
				$ad->setDateDeb(new DateTime('now'));
				$ad->setDateFin(DateHelper::calculDateFin(new DateTime('now')));

				//temporaire
				$ad->setASupprimer(false);
				//-----

				$em->persist($ad);
				$em->flush();
			}

			$this->addFlash('message', 'Demande enregistrée avec succès');
			return $this->redirectToRoute('user.demandes');
		}

		return $this->render('user/demande/edit.html.twig', [
			'applications' => $applications,
			'service' => $service
		]);
	}
}