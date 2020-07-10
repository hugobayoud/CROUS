<?php
namespace App\Entity;

use DateTime;
use App\Helper\DateHelper;
use App\Entity\ApplicationDemande;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ApplicationDemandes
{
	protected $description;

	protected $applicationDemandes;
	
    public function __construct()
    {
        $this->applicationDemandes = new ArrayCollection();
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
	}
	
    public function getApplicationDemandes()
    {
		return $this->applicationDemandes;
	}
	
	public function addApplicationDemande(ApplicationDemande $applicationDemande)
    {
        $this->applicationDemandes->add($applicationDemande);
    }

    public function removeApplicationDemande(ApplicationDemande $applicationDemande)
    {
        $this->applicationDemandes->removeElement($applicationDemande);
	}

	/**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
		$endDate = isset($_POST['endDate']) ? DateTime::createFromFormat("d/m/Y H:i:s", $_POST['endDate']) : NULL;
		DateHelper::validateMyDates($this->applicationDemandes, $context, $endDate, TRUE);
	}
}