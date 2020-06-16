<?php

namespace App\Entity;

use App\Entity\Service;
use App\Helper\DateHelper;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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
	 * @Assert\EqualTo(
	 * 		propertyPath="password", 
	 * 		message="Vous n'avez pas tapé le même mot de passe"
	 * 	)
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

    /**
     * @ORM\ManyToMany(targetEntity=Service::class, inversedBy="users")
     */
    private $services;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $activation_token;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $reset_token;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_deb_dsi;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
	private $date_fin_dsi;
	
	/**
     * @ORM\OneToMany(targetEntity=Validateur::class, mappedBy="id_user")
     */
    private $validateurs;

    public function __construct()
    {
		$this->services = new ArrayCollection();
		$this->validateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return strtoupper($this->nom);
    }

    public function setNom(string $nom): self
    {
        $this->nom = strtoupper($nom);

        return $this;
    }

    public function getPrenom(): ?string
    {
        return ucwords(strtolower($this->prenom));
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = ucwords(strtolower($prenom));

        return $this;
    }

    public function getEmail(): ?string
    {
        return strtolower($this->email);
    }

    public function setEmail(string $email): self
    {
        $this->email = strtolower($email);

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
     * @return Collection|Service[]
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
            $service->addUser($this);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->contains($service)) {
            $this->services->removeElement($service);
            $service->removeUser($this);
        }

        return $this;
	}

	public function getactivation_token(): ?string
	{
		return $this->getActivationToken();
	}
	
	public function getActivationToken(): ?string
	{
		return $this->activation_token;
	}

    public function setActivationToken(?string $activation_token): self
    {
        $this->activation_token = $activation_token;

        return $this;
	}
	
	/**
     * @return Collection|Validateur[]
     */
    public function getValidateurs(): Collection
    {
        return $this->validateurs;
    }

    public function addValidateur(Validateur $validateur): self
    {
        if (!$this->validateurs->contains($validateur)) {
            $this->validateurs[] = $validateur;
            $validateur->setIdUser($this);
        }

        return $this;
    }

    public function removeValidateur(Validateur $validateur): self
    {
        if ($this->validateurs->contains($validateur)) {
            $this->validateurs->removeElement($validateur);
            // set the owning side to null (unless already changed)
            if ($validateur->getIdUser() === $this) {
                $validateur->setIdUser(null);
            }
        }

        return $this;
    }



	/* AUTRES FONCTIONS !*/
	
    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
	}
	
	public function addRole(string $role): self
	{
		if (!in_array($role, $this->roles)) {
			$this->roles[] = $role;
		}
		
		return $this;
	}

	public function removeRole(string $roleToAdd): self
	{
		if (in_array($roleToAdd, $this->roles)) {
			$tab = [];
			foreach($this->roles as $role) {
				if($roleToAdd !== $role)
				$tab[] = $role;
			}

			$this->setRoles($tab);
		}
		
		return $this;
	}

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
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

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;

        return $this;
    }

    public function getDateDebDsi(): ?\DateTimeInterface
    {
        return $this->date_deb_dsi;
    }

    public function setDateDebDsi(?\DateTimeInterface $date_deb_dsi): self
    {
        $this->date_deb_dsi = $date_deb_dsi;

        return $this;
    }

    public function getDateFinDsi(): ?\DateTimeInterface
    {
        return $this->date_fin_dsi;
    }

    public function setDateFinDsi(?\DateTimeInterface $date_fin_dsi): self
    {
        $this->date_fin_dsi = $date_fin_dsi;

        return $this;
	}
	
	/* AUTRES FONCTIONS */

	/**
	 * Formate la date pour présentation sous la forme jj/mm/yyyy
	 * 
	 * @return string|NULL
	 */
	public function formatDate(\DateTime $date = NULL): ?string
	{
		return DateHelper::formatMyDate($date);
	}

	public function __toString()
    {
        return $this->email;
    }
}
