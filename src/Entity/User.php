<?php

namespace App\Entity;

use App\Entity\Dsi;
use App\Entity\Service;
use App\Helper\DateHelper;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use phpDocumentor\Reflection\Types\Boolean;
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
	 * @Assert\NotBlank(message="Un nom est obligatoire")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank(message="Un prénom est obligatoire")
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
	 * @Assert\Email(message="Veuillez entrer une adresse mail valide")
	 * @Assert\NotBlank(message="Veuillez entrer une adresse mail")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
	 * @Assert\Length(
	 * 		min="8", 
	 * 		minMessage="Votre mot de passe doit faire au moins 8 caractères",
	 * 	)
	 * @Assert\NotNull(message="Veuillez donner un mot de passe")
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
     * @ORM\Column(type="datetime")
     */
    private $date_deb_valid;

    /**
     * @ORM\Column(type="datetime")
     */
	private $date_fin_valid;

    /**
     * @ORM\ManyToMany(targetEntity=Service::class, inversedBy="users")
	 * @Assert\Count(
	 * 		min="1",
	 * 		minMessage="Veuillez sélectionner au moins un service"
	 * 	)
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
     * @ORM\OneToMany(targetEntity=Dsi::class, mappedBy="user", orphanRemoval=true)
     */
    private $dsis;

    /**
     * @ORM\OneToMany(targetEntity=Demande::class, mappedBy="user", orphanRemoval=true)
     */
    private $demandes;

    public function __construct()
    {
		$this->services = new ArrayCollection();
		$this->dsis = new ArrayCollection();
		$this->demandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return strtoupper($this->nom);
    }

    public function setNom(string $nom = null): self
    {
        $this->nom = strtoupper($nom);

        return $this;
    }

    public function getPrenom(): ?string
    {
        return ucwords(strtolower($this->prenom));
    }

    public function setPrenom(string $prenom = null): self
    {
        $this->prenom = ucwords(strtolower($prenom));

        return $this;
    }

    public function getEmail(): ?string
    {
        return strtolower($this->email);
    }

    public function setEmail(string $email = null): self
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

    public function addService(Service $service = NULL): self
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

    /**
     * @return Collection|Dsi[]
     */
    public function getDsis(): Collection
    {
        return $this->dsis;
    }

    public function addDsi(Dsi $dsi): self
    {
        if (!$this->dsis->contains($dsi)) {
            $this->dsis[] = $dsi;
            $dsi->setUser($this);
        }

        return $this;
    }

    public function removeDsi(Dsi $dsi): self
    {
        if ($this->dsis->contains($dsi)) {
            $this->dsis->removeElement($dsi);
            // set the owning side to null (unless already changed)
            if ($dsi->getUser() === $this) {
                $dsi->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Demande[]
     */
    public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(Demande $demande): self
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes[] = $demande;
            $demande->setUser($this);
        }

        return $this;
    }

    public function removeDemande(Demande $demande): self
    {
        if ($this->demandes->contains($demande)) {
            $this->demandes->removeElement($demande);
            // set the owning side to null (unless already changed)
            if ($demande->getUser() === $this) {
                $demande->setUser(null);
            }
        }

        return $this;
	}
	
	/**
	 * Retourne si l'agent est actuellement DSI ou non
	 * 
	 * @return bool : dsi ou non
	 */
	public function verifyCurrentDsi(): bool 
	{
		$currentDate = (new DateTime('now'))->getTimeStamp();
		foreach ($this->dsis as $dsi) {
			if ($currentDate > $dsi->getDateDeb()->getTimeStamp() && $currentDate < $dsi->getDateFin()->getTimeStamp()) {
				return TRUE;
			}
		}

		return FALSE;
	}
}
