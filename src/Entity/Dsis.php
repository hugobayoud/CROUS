<?php
namespace App\Entity;

use App\Entity\Dsi;
use Doctrine\Common\Collections\ArrayCollection;

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
}