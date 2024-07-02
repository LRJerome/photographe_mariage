<?php
namespace App\Controller\Admin\Category;

use App\Entity\Photos;
use DateTimeImmutable;
use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin')]
// Définition de la classe CategoryController qui étend AbstractController
class CategoryController extends AbstractController
{
    // Constructeur avec injection de dépendances
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryRepository $categoryRepository,
        private SluggerInterface $slugger
    ) {
    }

    // Route pour afficher la liste des catégories
    #[Route('/category/list', name: 'admin_category_index', methods: ['GET'])]
    public function index(): Response
    {
        // Rendu du template avec toutes les catégories
        return $this->render('pages/admin/category/index.html.twig', [
            "categories" => $this->categoryRepository->findAll()
        ]);
    }

    // Route pour créer une nouvelle catégorie
    #[Route('/category/create', name: 'admin_category_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        // Création d'une nouvelle instance de Category
        $category = new Category();
        
        // Création du formulaire
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        
        // Vérification de la soumission et de la validité du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Définition des dates de création et de mise à jour
            $category->setCreatedAt(new DateTimeImmutable());
            $category->setUpdatedAt(new DateTimeImmutable());
            
            // Récupération des fichiers image
            $imageFiles = $form->get('imageFiles')->getData();
            
            // Traitement des fichiers image s'il y en a
            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $photo = $this->handleImageUpload($category, $imageFile);
                    $this->em->persist($photo);
                    $category->addPhoto($photo);
                }
            }
            
            // Persistance de la catégorie
            $this->em->persist($category);
            $this->em->flush();
            
            // Rafraîchissement de l'entité
            $this->em->refresh($category);

            // Ajout d'un message flash de succès
            $this->addFlash("success", "La nouvelle catégorie a été ajoutée avec succès.");
            
            // Redirection vers la liste des catégories
            return $this->redirectToRoute("admin_category_index");
        }
        
        // Rendu du formulaire de création
        return $this->render('pages/admin/category/create.html.twig', [
            "form" => $form->createView()
        ]);
    }

    // Route pour éditer une catégorie existante
    #[Route('/category/{id<\d+>}/edit', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Category $category, Request $request): Response {
        // Création du formulaire d'édition
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        
        // Vérification de la soumission et de la validité du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour de la date de modification
            $category->setUpdatedAt(new DateTimeImmutable());

            // Gestion de la suppression des photos existantes
            if ($form->has('existingPhotos')) {
                $existingPhotos = $form->has('existingPhotos') ? $form->get('existingPhotos')->getData() : [];
                foreach ($category->getPhotos() as $photo) {
                    if (!isset($existingPhotos[$photo->getId()]) || $existingPhotos[$photo->getId()] === false) {
                        $category->removePhoto($photo);
                        $this->em->remove($photo);
                    }
                }
            }

            // Gestion de l'ajout de nouvelles photos
            if ($form->has('imageFiles')) {
                $imageFiles = $form->get('imageFiles')->getData();
                if ($imageFiles) {
                    foreach ($imageFiles as $imageFile) {
                        try {
                            $photo = $this->handleImageUpload($category, $imageFile);
                            $this->em->persist($photo);
                            $category->addPhoto($photo);
                        } catch (\Exception $e) {
                            $this->addFlash('error', "Erreur lors du téléchargement d'une image : " . $e->getMessage());
                        }
                    }
                }
            }

            // Sauvegarde des modifications
            $this->em->flush();
            $this->em->refresh($category);
        
            // Ajout d'un message flash de succès
            $this->addFlash("success", "La catégorie a été modifiée avec succès.");
        
            // Redirection vers la liste des catégories
            return $this->redirectToRoute("admin_category_index");
        }
    
        // Rendu du formulaire d'édition
        return $this->render("pages/admin/category/edit.html.twig", [
            "form" => $form->createView(),
            "category" => $category
        ]);
    }

    // Route pour supprimer une catégorie
    #[Route('/category/{id<\d+>}/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function delete(Category $category, Request $request): Response
    {
        // Vérification du jeton CSRF
        if ($this->isCsrfTokenValid("delete_category_" . $category->getId(), $request->request->get('csrf_token'))) {
            // Suppression de la catégorie
            $this->em->remove($category);
            $this->em->flush();

            // Ajout d'un message flash de succès
            $this->addFlash("success", "La catégorie a été supprimée.");
        }

        // Redirection vers la liste des catégories
        return $this->redirectToRoute("admin_category_index");
    }

    // Méthode privée pour gérer le téléchargement d'images
    private function handleImageUpload(Category $category, UploadedFile $imageFile): Photos
    {
        // Génération d'un nom de fichier unique et sécurisé
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

        try {
            // Déplacement du fichier vers le répertoire de destination
            $imageFile->move(
                $this->getParameter('photos_directory'),
                $newFilename
            );
            
            // Création d'une nouvelle instance de Photos
            $photo = new Photos();
            $photo->setImageName($newFilename);
            $photo->setCategory($category);
            $category->addPhoto($photo);
            
            return $photo;
        } catch (FileException $e) {
            throw new \Exception("Erreur lors du téléchargement de l'image : " . $e->getMessage());
        }    
    }

    // Route pour afficher les photos d'une catégorie
    /*
    #[Route('/category/{id}', name: 'admin_category_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showPhotos(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupération de la catégorie
        $category = $entityManager->getRepository(Category::class)->find($id);

        // Vérification de l'existence de la catégorie
        if (!$category) {
            throw $this->createNotFoundException('La catégorie demandée n\'existe pas');
        }

        // Récupération des photos de la catégorie
        $photos = $category->getPhotos();

        // Rendu de la vue avec les photos de la catégorie
        return $this->render('pages/user/category/photos.html.twig', [
            'category' => $category,
            'photos' => $photos,
        ]);
    }
    */
}
