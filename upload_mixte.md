## Peux tu me donner quelques exemples de code ( et me commenter chaque ligne) que je devrait mettre en place si j'adopte la solution hybride (php et javascript).

Certainement ! Voici quelques exemples de code commentés pour une solution hybride utilisant PHP (Symfony) et JavaScript pour gérer l'upload de photos dans votre projet de photographe de mariage :

1. Côté Symfony (PHP) :

```php
// src/Controller/PhotoUploadController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PhotoUploadController extends AbstractController
{
    /**
     * @Route("/upload", name="photo_upload", methods={"POST"})
     */
    public function upload(Request $request): JsonResponse
    {
        // Récupère le fichier uploadé
        $file = $request->files->get('photo');

        if (!$file) {
            return new JsonResponse(['error' => 'Aucun fichier reçu'], 400);
        }

        // Génère un nom de fichier unique
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        try {
            // Déplace le fichier dans le répertoire de destination
            $file->move(
                $this->getParameter('photos_directory'),
                $fileName
            );

            // Enregistre les informations du fichier en base de données
            // (à implémenter selon votre modèle de données)

            return new JsonResponse(['success' => true, 'filename' => $fileName]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de l'upload: '.$e->getMessage()], 500);
        }
    }
}
```

2. Côté JavaScript (avec Axios pour les requêtes AJAX) :

```javascript
// assets/js/photo-uploader.js
import axios from 'axios';

class PhotoUploader {
    constructor(inputElement, progressBarElement) {
        this.input = inputElement;
        this.progressBar = progressBarElement;
        this.initListeners();
    }

    initListeners() {
        // Écoute l'événement de changement sur l'input file
        this.input.addEventListener('change', this.handleFileSelect.bind(this));
    }

    handleFileSelect(event) {
        const files = event.target.files;
        for (let i = 0; i < files.length; i++) {
            this.uploadFile(files[i]);
        }
    }

    async uploadFile(file) {
        const formData = new FormData();
        formData.append('photo', file);

        try {
            const response = await axios.post('/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                },
                onUploadProgress: (progressEvent) => {
                    // Met à jour la barre de progression
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    this.updateProgressBar(percentCompleted);
                }
            });

            console.log('Upload réussi:', response.data);
            // Ici, vous pouvez ajouter du code pour afficher l'image uploadée
        } catch (error) {
            console.error('Erreur lors de l'upload:', error);
        }
    }

    updateProgressBar(percent) {
        this.progressBar.style.width = percent + '%';
        this.progressBar.textContent = percent + '%';
    }
}

// Initialisation de l'uploader
document.addEventListener('DOMContentLoaded', () => {
    const inputElement = document.getElementById('photo-input');
    const progressBarElement = document.getElementById('upload-progress');
    new PhotoUploader(inputElement, progressBarElement);
});
```

3. Template Twig :

```twig
{# templates/photo_upload/index.html.twig #}
{% extends 'base.html.twig' %}

{% block body %}
    <h1>Upload de photos</h1>
    <input type="file" id="photo-input" multiple accept="image/*">
    <div class="progress">
        <div id="upload-progress" class="progress-bar" role="progressbar"></div>
    </div>
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    {{ encore_entry_script_tags('photo-uploader') }}
{% endblock %}
```

Dans cet exemple :

1. Le contrôleur Symfony gère la réception et le stockage des fichiers côté serveur.
2. Le code JavaScript utilise une classe `PhotoUploader` pour gérer l'interface utilisateur et l'envoi des fichiers via AJAX.
3. Le template Twig fournit la structure HTML de base pour l'interface d'upload.

Cette approche hybride permet une expérience utilisateur fluide avec le traitement côté client en JavaScript, tout en bénéficiant de la robustesse et de la sécurité de Symfony pour le traitement côté serveur.

N'oubliez pas d'adapter ce code à votre structure de projet spécifique et d'ajouter des validations et des traitements supplémentaires selon vos besoins.

Citations:
[1] https://paiement.systempay.fr/doc/fr-FR/rest/V4.0/javascript/guide/example/code.html
[2] https://nouvelle-techno.fr/articles/2-live-coding-lier-les-fichiers-css-et-javascript-avec-symfony-5-1
[3] https://makina-corpus.com/symfony/creer-application-symfony-vuejs
[4] https://symfony.com/doc/5.x/frontend.html
[5] https://github.com/symfony/ux

<!-- ------------------------------ -->

````
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
    #[Vich\UploadableField(mapping: 'category', fileNameProperty: 'photo', size: 'imageSize')]
    private ?File $imageFile = null;
    #[ORM\OneToMany(targetEntity:Photos::class, mappedBy:"category", cascade:["persist", "remove"])]
    private Collection $photos;
    #[ORM\Column(nullable: true)]
    private ?array $photo = null;
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
    public function addPhoto(Photos $photo): self
{
     $this->photos[] = $photo;   
    return $this;
}
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
    public function removeImage(Photos $photo): self
    {
        if ($this->photos->removeElement($photo)) {
            // set the owning side to null (unless already changed)
            if ($photo->getCategory() === $this) {
                $photo->setCategory(null);
            }
        }
        return $this;
    }
    public function setPhotos($photos)
    {
        $this->photos = $photos;
        return $this;
    }   
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
````
<?php
namespace App\Form;
use App\Entity\Category;
use App\Form\PhotosFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
class CategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('photos', FileType::class, [
                'multiple' => true,
                'attr'     => [
                    'accept' => 'image/*',
                    'multiple' => 'multiple'
                ],
                ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
````
<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PhotosRepository;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: PhotosRepository::class)]
class Photos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;
    #[ORM\ManyToOne(targetEntity:Category::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;
    #[Vich\UploadableField(mapping: 'category', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adress = null;
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;
    public function __construct()
{
    $this->createdAt = new \DateTimeImmutable();
    $this->category = new ArrayCollection();
}
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCategory(): ?Category
    {
        return $this->category;
    }
    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }
    public function getAdress(): ?string
    {
        return $this->adress;
    }
    public function setAdress(?string $adress): static
    {
        $this->adress = $adress;
        return $this;
    }
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }
       public function setImageFile(? File $imageFile=null): void
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile){
            $this->createdAt = new \DateTimeImmutable();
    }
    }  
    public function getImageName() :?string
    {
        return $this->imageName;
    }
    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;
        return $this;
    }
}
```
<?php
namespace App\Form;
use App\Entity\Photos;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\OptionsResolver\OptionsResolver;
class PhotosFormType extends AbstractType
{ 
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {      
        $builder
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer',
                'download_uri' => false,
                'asset_helper' => true,
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Photos::class,
        ]);
    }
}
```


{{form_start(form)}}
				<div class="mb-3">
					<label for="category_form_name">
						Nom
						<span class="text-danger">*</span>
					</label>
					<div class="text-danger">{{form_errors(form.name)}}</div>
					{{form_widget(form.name, {'attr':{'class':'formulaire form-control','autofocus':'autofocus'}})}}
				</div>
				<div class="mb-3">
					<label for="category_form_description">
						Description
					</label>
					<div class="text-danger">{{form_errors(form.description)}}</div>
					{{form_widget(form.description, {'attr':{'class':'formulaire form-control', 'rows':'10'}})}}
				</div>
				<div class="mb-3">
					<label for="category_form_photos">
						Ajouter une ou des photos
					</label>
					<div class="text-danger">{{form_errors(form.description)}}</div>
					<div id="category_form_photos">
						{{form_row(form.photos, {'attr':{'class':'formulaire form-control', 'rows':'10'}})}}
						{# <button type="button" class="btn btn-primary mt-2" id="add_photo_button">Ajouter une image</button> #}
					</div>
				</div>
				<div class="mb-3 d-flex justify-content-between">
					<input formnovalidate type="submit" class="border border-dark btn F5EBE0 btn-block btn-lg mx-5" value="Créer">
					<a href="{{ path('admin_category_index') }}" class="border border-dark btn F5EBE0 btn-block btn-lg mx-5">Annuler</a>
				</div>
				{{form_end(form)}}

                ````








					{{ form_start(form) }}
        {{ form_row(form.name) }}
        {{ form_row(form.description) }}

        <div id="photos-collection">
            {{ form_label(form.photos) }}
            {{ form_errors(form.photos) }}
            <div data-prototype="{{ form_widget(form.photos.vars.prototype)|e('html_attr') }}">
                {% for photo in form.photos %}
                    <div class="photo-entry">
                        {{ form_widget(photo) }}
                        <button type="button" class="remove-photo">Supprimer</button>
                    </div>
                {% endfor %}
            </div>
        </div>

        <button type="button" id="add-photo">Ajouter une photo</button>

        <button type="submit">Enregistrer</button>
    {{ form_end(form) }}

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Sélectionne l'élément contenant le prototype des formulaires de photos
        var $collectionHolder = $('#photos-collection div[data-prototype]');
        
        // Récupère le prototype du formulaire de photo
        var prototype = $collectionHolder.data('prototype');
        
        // Compte le nombre d'enfants actuels dans la collection
        var index = $collectionHolder.children().length;

        // Ajoute un gestionnaire d'événements pour le bouton "Ajouter une photo"
        $('#add-photo').on('click', function() {
            // Remplace le placeholder __name__ par l'index actuel
            var newForm = prototype.replace(/__name__/g, index);
            
            // Ajoute le nouveau formulaire à la collection
            $collectionHolder.append('<div class="photo-entry">' + newForm + '<button type="button" class="remove-photo">Supprimer</button></div>');
            
            // Incrémente l'index pour le prochain formulaire
            index++;
        });

        // Ajoute un gestionnaire d'événements pour les boutons "Supprimer"
        $collectionHolder.on('click', '.remove-photo', function() {
            // Supprime l'entrée de photo la plus proche
            $(this).closest('.photo-entry').remove();
        });
    });

    ```