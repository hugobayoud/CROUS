<?php
namespace App\Controller;

use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

class HomeController extends AbstractController
{
	/**
	 * Si l'utilisateur n'est pas encore connecté, il est rédirigé vers la page de connexion.
	 * 
	 * @Route("/", name="home")
	 * @return Response
	 */
	public function index(): Response
	{
		$user = $this->getUser();

		if (is_null($user)) {
			return $this->redirectToRoute('security.login');
		} else {
			if ($user->isAdmin()) {
				return $this->redirectToRoute('admin.home');
			} else if ($user->isDSI()) {
				return $this->redirectToRoute('dsi.home');
			} else if ($user->isAValidator()) {
				return $this->redirectToRoute('valideur.home');
			} else {
				return $this->redirectToRoute('agent.home');
			}
		}

		return $this->render('index.html.twig');
	}

	/**
	 * Générer le PDF des droits effectifs des agents
	 * @Route("/toPDF/droits-effetifs", name="toPDF-droits-effetifs")
	 */
    public function droitsEffectifsToPDF(UserRepository $userRepo)
    {
		$currentDate = new DateTime('now');
		$date_html = $currentDate->format('d.m.Y');

        // Configure Dompdf according to your needs
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);
        
        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('toPDF/default.html.twig', [
			'titlePDF' => "CROUS Clermont-Auvergne, droits effectifs au $date_html",
			'users' => $userRepo->findAll()
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
		$dompdf->render();
		
		// Output the generated PDF to Browser (inline view)
        $dompdf->stream("CROUS-($date_html)-droits_effectifs.pdf", [
            "Attachment" => false
        ]);
    }
}