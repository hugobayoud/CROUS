<?php

namespace App\Controller;

use Exception;
use PDOException;
use App\Entity\Application;
use App\Form\ApplicationType;
use Doctrine\ORM\ORMException;
use Doctrine\DBAL\DBALException;
use App\Repository\ApplicationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/gestion-applications", name="admin.gestion-applications.")
 * 
 * @IsGranted("ROLE_ADMIN")
 */
class ApplicationController extends AbstractController
{
    /**
     * @Route("/", name="home", methods={"GET"})
     */
    public function index(ApplicationRepository $applicationRepo): Response
    {
        return $this->render('admin/gestion-applications/index.html.twig', [
            'applications' => $applicationRepo->findAllCodeASC(),
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
		try {
			if ($this->isCsrfTokenValid('delete'.$application->getId(), $request->request->get('_token'))) {
				$em = $this->getDoctrine()->getManager();
				$em->remove($application);
				$em->flush();
				$this->addFlash('message', 'Application (' . $application->getCode() . ') supprimée');
			}
        } catch (DBALException | PDOException | ORMException | Exception $e) {
            $this->addFlash('warning', 'Exception : L\'application ne peut être supprimé car il existe' . $application->countInDemands() . ' demande(s) et ' . $application->countInRights() . ' droit(s) effectif(s) en cours ou à venir pour cette application');
        }

        return $this->redirectToRoute('admin.gestion-applications.home');
    }
}
