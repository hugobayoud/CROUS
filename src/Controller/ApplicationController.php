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
 * @Route("/admin/application", name="admin.")
 */
class ApplicationController extends AbstractController
{
    /**
     * @Route("/", name="applications", methods={"GET"})
     */
    public function index(ApplicationRepository $applicationRepository): Response
    {
        return $this->render('admin/application/index.html.twig', [
            'applications' => $applicationRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="application.new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $application = new Application();
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($application);
            $entityManager->flush();

			$this->addFlash('message', 'L\'application (' . $application->getCode() . ') a bien été créée.');
            return $this->redirectToRoute('admin.applications');
        }

        return $this->render('admin/application/new.html.twig', [
            'application' => $application,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="application.show", methods={"GET"})
     */
    public function show(Application $application): Response
    {
        return $this->render('admin/application/show.html.twig', [
            'application' => $application,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="application.edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Application $application): Response
    {
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

			$this->addFlash('message', 'Modifications enregistrées avec succés (' . $application->getCode() . ')');
            return $this->redirectToRoute('admin.applications');
        }

        return $this->render('admin/application/edit.html.twig', [
            'application' => $application,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="application.delete", methods={"DELETE"})
     */
    public function delete(Request $request, Application $application): Response
    {
        if ($this->isCsrfTokenValid('delete'.$application->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($application);
            $entityManager->flush();
        }

		$this->addFlash('message', 'Application (' . $application->getCode() . ') supprimée');
        return $this->redirectToRoute('admin.applications');
    }
}
