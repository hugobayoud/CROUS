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
    private $date_deb_valid;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_fin_valid;

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

    public function getDateDebValid(): ?\DateTimeInterface
    {
        return $this->date_deb_valid;
    }

    public function setDateDebValid(\DateTimeInterface $date_deb_valid): self
    {
        $this->date_deb_valid = $date_deb_valid;

        return $this;
	}

	public function getDateFinValid(): ?\DateTimeInterface
    {
        return $this->date_fin_valid;
    }

    public function setDateFinValid(\DateTimeInterface $date_fin_valid): self
    {
        $this->date_fin_valid = $date_fin_valid;

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
