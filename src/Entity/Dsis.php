<?php
namespace App\Entity;

use DateTime;
use App\Entity\Dsi;
use App\Helper\DateHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Dsis
{
	protected $description;

	protected $dsis;
	
    public function __construct()
    {
        $this->dsis = new ArrayCollection();
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
	}
	
    public function getDsis()
    {
		return $this->dsis;
	}
	
	public function addDsi(Dsi $dsi)
    {
        $this->dsis->add($dsi);
    }

    public function removeDsi(Dsi $dsi)
    {
        $this->dsis->removeElement($dsi);
	}

	/**
     * @Assert\Callback
     */
	public function validate(ExecutionContextInterface $context, $payload)
    {
		$endDate = isset($_POST['endDate']) ? DateTime::createFromFormat("d/m/Y H:i:s", $_POST['endDate']) : NULL;
		DateHelper::validateMyDates($this->dsis, $context, $endDate);
    }
}