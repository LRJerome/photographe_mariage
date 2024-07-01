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
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères',
    )]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[Assert\Length(
        max: 255,
        maxMessage: 'La description ne doit pas dépasser {{ limit }} caractères',
    )]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    // rajour de samedi
    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'category', fileNameProperty: 'photo')]
    private ?File $imageFile = null;

    #[ORM\OneToMany(targetEntity:Photos::class, mappedBy:"category", cascade:["persist", "remove"])]
    private Collection $photos;

    #[ORM\Column(nullable: true)]
    private ?string $photo = null;
    // rajour de samedi
    
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;
    
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;
    
    public function __construct()
    {
        $this->photos = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    } 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

   /// rajout de samedi
    public function addPhoto(Photos $photo): self
{
    if (!$this->photos->contains($photo)) {
        $this->photos[] = $photo;
        $photo->setCategory($this);
    }
    return $this;
}

// -------------------
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * Get the value of photos
     */ 
    public function getPhotos(): Collection
    {
        return $this->photos;
    }
    public function addImage(Photos $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos[] = $photo;
            $photo->setCategory($this);
        }

        return $this;
    }
    public function removePhoto(Photos $photo): self
    {
        if ($this->photos->removeElement($photo)) {
            // set the owning side to null (unless already changed)
            if ($photo->getCategory() === $this) {
                $photo->setCategory(null);
            }
        }

        return $this;
    }
    /**
     * Set the value of photos
     *
     * @return  self
     */ 
    public function setPhotos($photos): self
    {
        $this->photos = $photos;

        return $this;
    }
     
    

    // rajour de samedi
    
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }


    
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    
    public function setPhoto($photo): self
    {
        $this->photo = $photo;

        return $this;
    }
}
