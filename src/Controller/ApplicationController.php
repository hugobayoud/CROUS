<?php

namespace App\Controller;

use App\Entity\Application;
use App\Form\ApplicationType;
use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/gestion-applications", name="admin.gestion-applications.")
 */
class ApplicationController extends AbstractController
{
    /**
     * @Route("/", name="home", methods={"GET"})
     */
    public function index(ApplicationRepository $applicationRepo): Response
    {
        return $this->render('admin/gestion-applications/index.html.twig', [
            'applications' => $applicationRepo->findAll(),
        ]);
    }

    /**
     * @Route("/nouveau", name="nouvelle-application", methods={"GET","POST"})
     */
    public function newApplication(Request $request): Response
    {
        $application = new Application();
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			
			if (isset($_POST['transverse_checkbox'])) {
				$application->setType('t');
			} else {
				$application->setType('p');
			}

            $em->persist($application);
            $em->flush();

			$this->addFlash('message', 'L\'application (' . $application->getCode() . ') a bien été créée.');
            return $this->redirectToRoute('admin.gestion-applications.home');
        }

        return $this->render('admin/gestion-applications/new.html.twig', [
            'application' => $application,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="voir-application", methods={"GET"})
     */
    public function showApplication(Application $application): Response
    {
        return $this->render('admin/gestion-applications/show.html.twig', [
            'application' => $application,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="editer-application", methods={"GET","POST"})
     */
    public function edit(Request $request, Application $application): Response
    {
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			
			if (isset($_POST['transverse_checkbox'])) {
				$application->setType('t');
			} else {
				$application->setType('p');
			}

			$em->persist($application);
			$em->flush();

			$this->addFlash('message', 'Modifications enregistrées avec succés (' . $application->getCode() . ')');
            return $this->redirectToRoute('admin.gestion-applications.home');
        }

        return $this->render('admin/gestion-applications/edit.html.twig', [
            'application' => $application,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/supprimer", name="supprimer-application", methods={"DELETE"})
     */
    public function deleteApplication(Request $request, Application $application): Response
    {
        if ($this->isCsrfTokenValid('delete'.$application->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($application);
            $entityManager->flush();
        }

		$this->addFlash('message', 'Application (' . $application->getCode() . ') supprimée');
        return $this->redirectToRoute('admin.gestion-applications.home');
    }
}
