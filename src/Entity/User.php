<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 * 		fields={"email"},
 * 		message="email déjà utilisé"
 * 	)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
	 * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
	 * @Assert\Length(
	 * 		min="8", 
	 * 		minMessage="Votre mot de passe doit faire au moins 8 caractères",
	 * 	)
     */
    private $password;

	/**
	 * @Assert\EqualTo(propertyPath="password", message="Vous n'avez pas tapé le même mot de passe")
	 */
	public $confirm_password;
	
    /**
     * @ORM\Column(type="boolean")
     */
    private $dsi;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getDsi(): ?bool
    {
        return $this->dsi;
    }

    public function setDsi(bool $dsi): self
    {
        $this->dsi = $dsi;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
	}
	
	/**
     * @return (Role|string)[] The user roles
     */
	public function getRoles() 
	{
		return ['ROLE_USER'];
	}

    /**
     * @return string|null The salt
     */
    public function getSalt() {}

	public function eraseCredentials() {}
	
	/**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername() {}
}
