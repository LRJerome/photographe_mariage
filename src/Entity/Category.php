<?php

namespace App\Entity;

use App\Entity\Photos;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[UniqueEntity('name', message:"Cette categorie exite déja.")]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    // Identifiant unique de la catégorie
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Nom de la catégorie, obligatoire et unique
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères',
    )]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    // Description de la catégorie, optionnelle
    #[Assert\Length(
        max: 255,
        maxMessage: 'La description ne doit pas dépasser {{ limit }} caractères',
    )]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    // Fichier image temporaire pour l'upload
    #[Vich\UploadableField(mapping: 'category', fileNameProperty: 'photo')]
    private ?File $imageFile = null;

    // Collection de photos associées à la catégorie
    #[ORM\OneToMany(targetEntity:Photos::class, mappedBy:"category", cascade:["persist", "remove"])]
    private Collection $photos;

    // Nom du fichier photo de la catégorie
    #[ORM\Column(nullable: true)]
    private ?string $photo = null;
    
    // Date de création de la catégorie
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;
    
    // Date de dernière mise à jour de la catégorie
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;
    
    // Constructeur de la classe
    public function __construct()
    {
        $this->photos = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    } 

    // Getter pour l'id
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter pour le nom
    public function getName(): ?string
    {
        return $this->name;
    }

    // Setter pour le nom
    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    // Getter pour la description
    public function getDescription(): ?string
    {
        return $this->description;
    }

    // Setter pour la description
    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    // Méthode pour ajouter une photo à la collection
    public function addPhoto(Photos $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos[] = $photo;
            $photo->setCategory($this);
        }
        return $this;
    }

    // Setter pour le fichier image
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {

            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    // Getter pour le fichier image
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    // Getter pour la collection de photos
    public function getPhotos(): Collection
    {
        return $this->photos;
    }
    /*
    public function addImage(Photos $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos[] = $photo;
            $photo->setCategory($this);
        }

        return $this;
    }
    */
    // Méthode pour retirer une photo de la collection
    public function removePhoto($photo): self
    {
        if ($this->photos->removeElement($photo)) {
            // set the owning side to null (unless already changed)
            if ($photo->getCategory() === $this) {
                $photo->setCategory(null);
            }
        }
        return $this;
    }

    // Setter pour remplacer toute la collection de photos
    public function setPhotos($photos): self
    {
            // Supprimer toutes les anciennes photos
        foreach ($this->photos as $photo) {
            $this->removePhoto($photo);
        }
        // Ajouter les nouvelles photos
        foreach ($photos as $photo) {
            $this->addPhoto($photo);
        }
        return $this;
    }

    // Getter pour la date de création
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    // Setter pour la date de création
    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    // Getter pour la date de mise à jour
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Setter pour la date de mise à jour
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }  

    // Getter pour le nom du fichier photo
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    // Setter pour le nom du fichier photo
    public function setPhoto($photo): self
    {
        $this->photo = $photo;
        return $this;
    }
}