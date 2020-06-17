<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Users
{
    protected $description;
	protected $users;

    public function __construct()
    {
		$this->users = new ArrayCollection();
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getUsers()
    {
        return $this->users;
	}
	
	public function removeUser(User $user)
    {
        $this->users->removeElement($user);
	}
}
