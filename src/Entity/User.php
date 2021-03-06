<?php

namespace App\Entity;

use DateTime;
use App\Entity\Dsi;
use App\Entity\Couple;
use App\Entity\Service;
use App\Entity\Valideur;
use App\Helper\DateHelper;
use App\Helper\HtmlHelper;
use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\OneToMany(targetEntity=Dsi::class, mappedBy="user", orphanRemoval=true, cascade={"remove"})
	 * @ORM\OrderBy({"date_deb" = "ASC"})
     */
	private $dsis;
	
    /**
     * @ORM\OneToMany(targetEntity=Valideur::class, mappedBy="user", orphanRemoval=true, cascade={"remove"})
	 * @ORM\OrderBy({"date_deb" = "ASC"})
     */
    private $valideurs;

    /**
     * @ORM\OneToMany(targetEntity="Demande", mappedBy="user", orphanRemoval=true, cascade={"remove"})
	 * @ORM\OrderBy({"created_at" = "ASC"})
     */
	private $demandes;

	/**
     * @ORM\OneToMany(targetEntity=Couple::class, mappedBy="user", orphanRemoval=true, cascade={"remove"})
     */
	private $couples;


    public function __construct()
    {
		$this->services = new ArrayCollection();
		$this->dsis = new ArrayCollection();
		$this->valideurs = new ArrayCollection();
		$this->demandes = new ArrayCollection();
		$this->couples = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return mb_strtoupper($this->nom);
    }

    public function setNom(string $nom = null): self
    {
        $this->nom = mb_strtoupper($nom);

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
	
	public function getServicesWhereValidator(): Array
	{
		$services = [];

		foreach ($this->services as $service) {
			if ($this->isValidator($service->getId())) {
				$services[] = $service;
			}
		}

        return $services;
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
	 * Role de l'user
     * @see UserInterface
     */
    public function getRoles(): array
    {
		$roles = $this->roles;
		
		$roles[] = 'ROLE_USER';
		
		if ($this->isDSI()) {
			$roles[] = 'ROLE_DSI';
		}

		if ($this->isAValidator()) {
			$roles[] = 'ROLE_A_VALIDATOR';
		}
		
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

	/**
	 * Formate la date pour présentation sous la forme jj/mm/yyyy HH:mm:ss
	 * 
	 * @return string
	 */
	public function fullyFormatDate(\DateTime $date = NULL): string
	{
		return DateHelper::fullyFormatMyDate($date);
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
     * @return Collection|Valideur[]
     */
    public function getValideurs(): Collection
    {
        return $this->valideurs;
    }

    public function addValideur(Valideur $valideur): self
    {
        if (!$this->valideurs->contains($valideur)) {
            $this->valideurs[] = $valideur;
            $valideur->setUser($this);
        }

        return $this;
    }

    public function removeValideur(Valideur $valideur): self
    {
        if ($this->valideurs->contains($valideur)) {
            $this->valideurs->removeElement($valideur);
            // set the owning side to null (unless already changed)
            if ($valideur->getUser() === $this) {
                $valideur->setUser(null);
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
	
	/**
     * @return Demande|NULL
     */
    public function getDemande(int $serviceId): ?Demande
    {
		foreach ($this->demandes as $demande) {
			if ($demande->getService()->getId() === $serviceId) {
				return $demande;
			}
		}

        return NULL;
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
     * @return Collection|Couple[]
     */
    public function getCouples(): Collection
    {
        return $this->couples;
	}
	
	/**
     * @return Couple|NULL
     */
    public function getCouple(int $serviceId): ?Couple
    {
		foreach ($this->couples as $couple) {
			if ($couple->getService()->getId() === $serviceId) {
				return $couple;
			}
		}

        return NULL;
    }

    public function addCouple(Couple $couple): self
    {
        if (!$this->couples->contains($couple)) {
            $this->couples[] = $couple;
            $couple->setUser($this);
        }

        return $this;
    }

    public function removeCouple(Couple $couple): self
    {
        if ($this->couples->contains($couple)) {
            $this->couples->removeElement($couple);
            // set the owning side to null (unless already changed)
            if ($couple->getUser() === $this) {
                $couple->setUser(null);
            }
        }

        return $this;
	}

	/**
	 * Retourne si l'agent est un administrateur du site ou non
	 * L'ajout du rôle administrateur ne se fait qu'en base de données
	 * @return bool
	 */
	public function isAdmin(): bool
	{
		return in_array('ROLE_ADMIN', $this->roles);
	}
	
	/**
	 * Retourne si l'agent est actuellement DSI ou non
	 * @return bool : dsi ou non
	 */
	public function isDSI(): bool 
	{
		$currentDate = new DateTime('now');
		foreach ($this->dsis as $dsi) {
			if ($currentDate >= $dsi->getDateDeb() && $currentDate <= $dsi->getDateFin()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retourne si l'agent est actuellement valideur d'un service ou non
	 * @return bool : valideur ou non
	 */
	public function isValidator(int $serviceId): bool 
	{
		$now = new DateTime('now');
		foreach ($this->valideurs as $valideur) {
			if ($valideur->getService()->getId() === $serviceId) {
				if ($now >= $valideur->getDateDeb() && $now <= $valideur->getDateFin()) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Retourne si l'utilisateur est un valideur pour au moins un service
	 * @return bool
	 */
	public function isAValidator()
	{
		$now = new DateTime('now');
		foreach ($this->valideurs as $valideur) {
			if ($now >= $valideur->getDateDeb() && $now <= $valideur->getDateFin()) {
				return true;
			}
		}

		return false;
	}

	/** 
	 * Retourne le nombre de demandes en cours que doit valider un valideur d'un service
	 * @return int
	 */
	public function countToValidate()
	{
		$count = 0;

		foreach ($this->services as $service) {
			if ($this->isValidator($service->getId())) {
				foreach ($service->getDemandes() as $demande) {
					if ($demande->getEtat() === 0) {
						$count++;
					}
				}
			}
		}

		return $count;
	}

	/* TO HTML FUNCTIONS */
	
	/**
	 * Retourne les informations sur l'user en HTML
	 * @return string
	 */
	public function userInfo_html(): string
	{
		return HtmlHelper::userInfo($this);
	}
	
	/**
	 * Retourne si l'user est DSI ou non en HTML
	 * @return string
	 */
	public function dsi_html(): string
	{
		return HtmlHelper::dsi($this);
	}

	/**
	 * Retourne le libelle court d'un service d'un user valideur et de sa fin de contrat dans celui-ci sous forme de string
	 * @return string
	 */
	public function valideur_html(): string
	{
		return HtmlHelper::servicesWhereValidator($this);
	}

	/**
	 * Retourne la liste des droits effectifs de l'user en HTML
	 * @return string
	 */
	public function droitsEffectifs_html(): string
	{
		return HtmlHelper::droitsEffectifs($this);
	}

	/* POUR PDF */
	/**
	 * Retourne les informations sur l'user pour PDF
	 * @return string
	 */
	public function userInfo_toPDF(): string
	{
		return HtmlHelper::userInfo_toPDF($this);
	}

	/**
	 * Retourne la liste des droits effectifs de l'user pour PDF
	 * @return string
	 */
	public function droitsEffectifs_toPDF(): string
	{
		return HtmlHelper::droitsEffectifs_toPDF($this);
	}

	/**
	 * Renvoie le numéro de téléphone associé à un couple (user, service) en droit effectif
	 * @return string|NULL
	 */
	public function getPhoneByService(int $serviceId): ?string
	{
		$couple = $this->getCouple($serviceId);

		if (!is_null($couple)) {
			$phone = $couple->getTelephone();

			if (!is_null($phone)) {
				return htmlentities($phone);
			}
		}
		return NULL;
	}

	/**
	 * Renvoie la liste des adresses mails que recoit l'agent (qui fait la demande)
	 * @return string|NULL
	 */
	public function getMailsByService(int $serviceId): ?string
	{
		$couple = $this->getCouple($serviceId);

		if (!is_null($couple)) {
			$mails = $couple->getMailDe();

			if (!is_null($mails)) {
				return htmlentities($mails);
			}
		}
		return NULL;
	}

	/**
	 * Renvoie la liste des répertoires serveus auxquels souhaite accéder l'agent (qui fait une demande)
	 * @return string|NULL
	 */
	public function getFoldersByService(int $serviceId): ?string
	{
		$couple = $this->getCouple($serviceId);

		if (!is_null($couple)) {
			$folders = $couple->getRepertoiresServeur();

			if (!is_null($folders)) {
				return htmlentities($folders);
			}
		}
		return NULL;	
	}

	/**
	 * Retourne le nombre total de droits effectifs qui vont bientôt se terminer dans tous les services dans lesquels l'user est valideur
	 * @return int
	 */
	public function countFinishedSoon(): int
	{
		$count = 0;
		
		foreach ($this->services as $service) {
			if($this->isValidator($service->getId())) {
				$count += $service->countFinishedSoon();
			}
		}

		return $count;
	}

	/**
	 * Compte le nombre pour être valideur
	 * @return int
	 * 		0 	: Il ne sera jamais valideur
	 * 		-x 	: Il est actuellement valideur et le reste pendant x jours
	 * 		x 	: Il n'est pas encore valideur mais l'est dans x jours
	 */
	public function countDaysBeforeValidator(int $serviceId): int
	{
		$now = new DateTime('now');
		$count = 999;

		foreach ($this->valideurs as $valideur) {
			if ($valideur->getService()->getId() === $serviceId) {
				// S'il est actuellement valideur, on calcule x négatif
				if ($now >= $valideur->getDateDeb() && $now <= $valideur->getDateFin()) {
					return -(int)date_diff($now, $valideur->getDateFin())->format('%a');
				// Sinon, si il va devenir valideur, on prend l'enregistrement le plus proche
				} else if ($now < $valideur->getDateDeb() && $count > (int)date_diff($now, $valideur->getDateDeb())->format('%a')) {
					$count = (int)date_diff($now, $valideur->getDateDeb())->format('%a');
				}
			}
		}

		return $count === 999 ? 0 : $count;
	}

	/**
	 * Obtenir la date de fin de la fonction DSI pour un agent
	 * @return string|NULL
	 */
	public function getDateFinDSI(): ?string
	{
		$currentDate = new DateTime('now');
		foreach ($this->dsis as $dsi) {
			if ($currentDate >= $dsi->getDateDeb() && $currentDate <= $dsi->getDateFin()) {
				return $dsi->getDateFin()->format('d/m/Y');
			}
		}
	}
}