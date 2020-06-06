<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Helper\DateHelper;
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
	public function registration(UserPasswordEncoderInterface $encoder, Request $request, EntityManagerInterface $em)
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
			$hash = $encoder->encodePassword($user, $user->getPassword());
			$user->setPassword($hash);
			$em->persist($user);
			$em->flush();

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
}
