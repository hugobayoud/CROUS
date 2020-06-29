<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApplicationRepository::class)
 * @UniqueEntity(
 * 		fields={"code"},
 * 		message="Code déjà utilisé pour une autre application"
 * 	)
 */
class Application
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
	 * @Assert\Length(
	 * 		min=1,
	 * 		minMessage="Le code d'un application doit faire entre 1 et 10 caractères",
	 * 		max=10,
	 * 		maxMessage="Le code d'une application doit faire entre 1 et 10 caractères"
	 * 	)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
	 * @Assert\Length(
	 * 		max=50,
	 * 		maxMessage="Le libellé d'une application doit ne doit pas dépasser 50 caractères"
	 * 	)
     */
    private $libelle;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return ucwords(strtolower($this->libelle));
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
	}
	
	/* AUTRES FONCTIONS */
	public function __toString()
	{
		return $this->code;
	}
}
