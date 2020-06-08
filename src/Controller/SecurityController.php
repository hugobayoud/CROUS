<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Helper\DateHelper;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
	/**
	 * @Route("/inscription", name="security.registration")
	 */
	public function registration(UserPasswordEncoderInterface $encoder, Request $request, EntityManagerInterface $em, \Swift_Mailer $mailer)
	{
		$user = new User();
		//Par défaut, dateDebValid à la date de l'incription et dsi sur false, calcul de dateFinValid
		$user
			->setDateDebValid(new \DateTime(null, new \DateTimeZone('Europe/Paris')))
			->setDateFinValid(DateHelper::calculDateFin($user->getDateDebValid()))
			->setDsi(0);

		
		$form = $this->createForm(RegistrationType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			// On génère le token d'activation. 
			// Si en BDD, le champ activation_token de l'user n'est paas NULL, 
			// alors le compte n'est pas encore vérifié par un DSI
			$user->setActivationToken(md5(uniqid()));

			// On hash le mot de passe
			$hash = $encoder->encodePassword($user, $user->getPassword());
			$user->setPassword($hash);

			// On envoie les données dans la BDD
			$em->persist($user);
			$em->flush();

			// On envoi un mail a un agent de la dsi pour validation
			$messageUser = (new \Swift_Message("Activation de votre compte demandes d'ouverture d'acces aux outils informatiques"))
				->setFrom('adresse.dsi@crous.fr')
				->setTo($user->getEmail())
				->setBody(
					$this->renderView(
						'emails/activation_user.html.twig',
						['user' => $user]
					),
					'text/html'
				);

			// On envoie le message
			$mailer->send($messageUser);


			// On envoi un mail à l'user lui demandant d'attendre qu'un DSI valide
			$messageDSI = (new \Swift_Message('Nouvel utilisateur a valider'))
				->setFrom($user->getEmail())
				->setTo('valideur.dsi@crous.fr')
				->setBody(
					$this->renderView(
						'emails/activation_dsi.html.twig', 
						[
							'user' => $user, 
							'token' => $user->getActivationToken()
						]						
					),
					'text/html'
				);
			// On envoie le message
			$mailer->send($messageDSI);

			$this->addFlash('message', 'Demande de création de compte bien envoyée. Vous pourrez vous connecter quand un agent de la DSI/Administrateur aura confirmé votre compte.');

			return $this->redirectToRoute("security.login");
		}

		return $this->render('security/registration.html.twig', [
			'form' => $form->createView()
		]);
	}

	/**
	 * @Route("/connexion", name="security.login")
	 */
	public function login()
	{
		return $this->render('security/login.html.twig');
	}

	/**
	 * @Route("/deconnexion", name="security.logout")
	 */
	public function logout()
	{
		
	}

	/**
	 * Pour activer le compte de l'user à partir d'un mail envoyé
	 * @Route("/activation/{token}", name="security.activation")
	 */
	public function activation($token, UserRepository $userRepo)
	{
		// Est-ce qu'un utilisateur a ce token
		$user = $userRepo->findOneBy(['activation_token' => $token]);

		// Si aucun utilisateur n'existe pas ce token
		if (!$user) {
			throw $this->createNotFoundException('Cet utilisateur n\'existe pas, token introuvable');
		}

		// On supprime le token
		$user->setActivationToken(NULL);
		$em = $this->getDoctrine()->getManager();
		$em->persist($user);
		$em->flush();

		// On envoie un message flash
		// $this->addFlash('message', "Compte activé");

		return $this->redirectToRoute('home');
	}
}
