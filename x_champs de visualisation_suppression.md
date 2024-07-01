Pour ajouter des champs de visualisation et de suppression d'images à votre formulaire en utilisant le bundle LiipImagineBundle, vous pouvez procéder comme suit :

1. D'abord, assurez-vous que LiipImagineBundle est correctement installé et configuré dans votre projet Symfony.

2. Modifiez votre formulaire pour inclure un champ de collection pour les images existantes :

```php
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('name', TextType::class)
        ->add('description', TextareaType::class)
        ->add('existingImages', CollectionType::class, [
            'entry_type' => ExistingImageType::class,
            'allow_delete' => true,
            'by_reference' => false,
            'label' => false,
        ])
        ->add('imageFiles', FileType::class, [
            'label' => 'Nouvelles images',
            'multiple' => true,
            'mapped' => false,
            'required' => false,
            'attr' => [
                'accept' => 'image/*',
                'multiple' => 'multiple'
            ],
            'constraints' => [
                new \Symfony\Component\Validator\Constraints\All([
                    new \Symfony\Component\Validator\Constraints\Image([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, GIF)',
                    ])
                ])
            ],
        ]);
}
```

3. Créez un nouveau type de formulaire pour les images existantes :

```php
// src/Form/ExistingImageType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class ExistingImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('delete', CheckboxType::class, [
                'label' => 'Supprimer',
                'required' => false,
            ]);
    }
}
```

4. Dans votre template Twig, affichez les images existantes et le formulaire :

```twig
{{ form_start(form) }}
    {{ form_row(form.name) }}
    {{ form_row(form.description) }}

    <h3>Images existantes</h3>
    {% for imageForm in form.existingImages %}
        <div>
            <img src="{{ asset(imageForm.vars.value.path) | imagine_filter('thumbnail') }}" alt="Image">
            {{ form_row(imageForm.delete) }}
            {{ form_widget(imageForm.id) }}
        </div>
    {% endfor %}

    {{ form_row(form.imageFiles) }}

    <button type="submit">Enregistrer</button>
{{ form_end(form) }}
```

5. Dans votre contrôleur, gérez la suppression des images :

```php
public function edit(Request $request, Category $category): Response
{
    $form = $this->createForm(CategoryType::class, $category);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Gérer la suppression des images
        foreach ($form->get('existingImages') as $imageForm) {
            if ($imageForm->get('delete')->getData()) {
                $image = $this->entityManager->getRepository(Image::class)->find($imageForm->get('id')->getData());
                if ($image) {
                    $category->removeImage($image);
                    $this->entityManager->remove($image);
                }
            }
        }

        // Gérer l'ajout de nouvelles images
        $imageFiles = $form->get('imageFiles')->getData();
        foreach ($imageFiles as $imageFile) {
            // Logique pour ajouter de nouvelles images...
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('category_index');
    }

    return $this->render('category/edit.html.twig', [
        'form' => $form->createView(),
        'category' => $category,
    ]);
}
```

Cette approche vous permettra d'afficher les images existantes avec LiipImagineBundle, de les supprimer si nécessaire, et d'ajouter de nouvelles images. Assurez-vous d'adapter le code à votre structure spécifique d'entités et de formulaires.