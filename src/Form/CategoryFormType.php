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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
    // Ajoute un champ de texte pour le nom
    ->add('name', TextType::class)

    // Ajoute un champ de texte multiligne pour la description
    ->add('description', TextareaType::class)

    // Ajoute une collection de cases à cocher pour les photos existantes
    ->add('existingPhotos', CollectionType::class, [
        'entry_type' => CheckboxType::class,  // Chaque élément de la collection est une case à cocher
        'allow_add' => false,  // Ne permet pas d'ajouter de nouveaux éléments dynamiquement
        'allow_delete' => true,  // Permet de supprimer des éléments
        'prototype' => false,  // N'utilise pas de prototype pour ajouter de nouveaux éléments
        'required' => false,  // Le champ n'est pas obligatoire
        'label' => false,  // N'affiche pas de label pour ce champ
        'mapped' => false,  // Ne mappe pas ce champ à une propriété de l'entité
    ])

    // Ajoute un champ de type fichier pour télécharger de nouvelles images
    ->add('imageFiles', FileType::class, [
        'label' => 'Images',  // Label du champ
        'multiple' => true,  // Permet de sélectionner plusieurs fichiers
        'mapped' => false,  // Ne mappe pas ce champ à une propriété de l'entité
        'required' => false,  // Le champ n'est pas obligatoire
        'attr' => [
            'accept' => 'image/*',  // Accepte tous les types d'images
            'multiple' => 'multiple'  // Permet la sélection multiple dans l'interface HTML
        ],
        'constraints' => [
            new \Symfony\Component\Validator\Constraints\All([  // Applique les contraintes à chaque fichier
                new \Symfony\Component\Validator\Constraints\Image([
                    'maxSize' => '10M',  // Taille maximale de 10 Mo
                    'mimeTypes' => [  // Types MIME autorisés
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                    ],
                    'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, GIF)',  // Message d'erreur personnalisé
                ])
            ])
        ],
    ]);
}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
