<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Form\ResetPassType;
use App\Helper\DateHelper;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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
	public function login(AuthenticationUtils $authenticationUtils): Response
	{
		// get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
	}

	/**
	 * @Route("/deconnexion", name="security.logout")
	 */
	public function logout() {}

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

	/**
	 * @Route("/oubli-pass", name="security.forgotten_password")
	 */
	public function forgottenPassword(Request $request, UserRepository $userRepo, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator)
	{
		$form = $this->createForm(ResetPassType::class);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$email = ($form->getData())['email'];

			// On cherche si un utilisateur a l'email qui vient de mettre
			$user = $userRepo->findOneByEmail($email);

			if (!$user) {
				// On envoie un message flash
				$this->addFlash('warning', "cette adresse n'est pas enregistrée dans la BDD");

				return $this->redirectToRoute('security.forgotten_password');
			} else {
				// On génère un token
				$token = $tokenGenerator->generateToken();

				// On essaie decrire en BDD, si on y arrive pas, pas besoin de dire à l'user qu'on va lui envoyer un mail
				try {
					$user->setResetToken($token);
					$em = $this->getDoctrine()->getManager();
					$em->persist($user);
					$em->flush();
				} catch(\Exception $e) {
					$this->addFlash('warning', 'Attention : une erreur est survenue : ' . $e->getMessage());

					return $this->redirectToRoute('security.forgotten_password');
				}
				

				//On gnénère l'url de réinitialisation
				$url = $this->generateUrl('security.reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

				// On envoie le mail
				$message = (new \Swift_Message('Réinitilisez votre mot de passe'))
					->setFrom('no-reply@crous.fr')
					->setTo($user->getEmail())
					->setBody(
						'<p>Vous avez fait une demande de réinitilisation de mot de passe. Veuillez suivre le lien suivant : <a href="' . $url . '">ici</a></p>"',
						'text/html'
					);

				$mailer->send($message);

				// Message flash de confirmation
				$this->addFlash('message', 'Un e-mail de réinitilisation de mot de passe vous a été envoyé');

				return $this->redirectToRoute('security.login');
			}
		}

		return $this->render('security/forgotten_password.html.twig', [
			'form' => $form->createView()
		]);
	}

	/**
	 * @Route("/reset_password/{token}", name="security.reset_password")
	 */
	public function resetPassword($token, Request $request, UserPasswordEncoderInterface $encoder)
	{
		// On cherche l'utilisateur avec le token fourni
		$user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['reset_token' => $token]);

		if (!$user) {
			$this->addFlash('warning', 'Aucun user trouvé. Ce token ne correspond à aucun utilisateur en base de données');
			return $this->redirectToRoute('security.login');
		}

		// On vérifie que le formulaire est envoyé en méthode POST
		if ($request->isMethod('POST')) {
			// On supprime le token
			$user->setResetToken(NULL);

			// On chiffre le nouveau mot de passe
			$user->setPassword($encoder->encodePassword($user, $request->request->get('password')));

			// On persiste et envoie en BDD
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();

			$this->addFlash('message', 'Mot de passe modifié avec succès');

			return $this->redirectToRoute('security.login');
		}

		return $this->render('security/reset_password.html.twig', ['reset_token' => $token]);
	}
}
