<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Repository\ServiceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/gestion/services", name="admin.gestion-services.")
 * 
 * @IsGranted("ROLE_ADMIN")
 */
class ServiceController extends AbstractController
{
    /**
     * @Route("/", name="home", methods={"GET"})
     */
    public function index(ServiceRepository $serviceRepo): Response
    {
        return $this->render('admin/gestion-services/index.html.twig', [
            'services' => $serviceRepo->findAll(),
        ]);
    }

    /**
     * @Route("/nouveau", name="nouveau-service", methods={"GET","POST"})
     */
    public function newService(Request $request): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($service);
            $entityManager->flush();

			$this->addFlash('message', 'Le service (' . $service->getCode() . ') a bien été créé.');
            return $this->redirectToRoute('admin.gestion-services.home');
        }

        return $this->render('admin/gestion-services/new.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="voir-service", methods={"GET"})
     */
    public function showService(Service $service): Response
    {
        return $this->render('admin/gestion-services/show.html.twig', [
            'service' => $service,
        ]);
    }

    /**
     * @Route("/{id}/editer", name="editer-service", methods={"GET","POST"})
     */
    public function editService(Request $request, Service $service): Response
    {
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

			$this->addFlash('message', 'Modifications enregistrées avec succés (' . $service->getCode() . ')');
            return $this->redirectToRoute('admin.gestion-services.home');
        }

        return $this->render('admin/gestion-services/edit.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/supprimer", name="supprimer-service", methods={"DELETE"})
     */
    public function deleteService(Request $request, Service $service): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($service);
            $entityManager->flush();
		}
		
		$this->addFlash('message', 'Service (' . $service->getCode() . ') supprimé');
        return $this->redirectToRoute('admin.gestion-services');
    }
}
