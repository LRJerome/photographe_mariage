<?php
// Ce controller gère toutes les opérations liées aux catégories dans la partie admin du site.
// Il permet de lister, créer, éditer et supprimer des catégories, ainsi que de gérer les images associées.

namespace App\Controller\Admin\Category;

// Importation des classes nécessaires
use App\Entity\Photos;
use DateTimeImmutable;
use App\Entity\Category;
use App\Form\CategoryFormType;
use Symfony\Component\Finder\Finder;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class CategoryController extends AbstractController
{
    // Le constructeur utilise l'injection de dépendances pour récupérer les services dont on a besoin
    public function __construct(
        private EntityManagerInterface $em, // Pour interagir avec la base de données
        private CategoryRepository $categoryRepository, // Pour effectuer des requêtes sur les catégories
        private SluggerInterface $slugger // Pour créer des slugs (utile pour les noms de fichiers)
    ) {
    }

    // Cette route affiche la liste de toutes les catégories
    #[Route('/admin/category/list', name: 'admin_category_index', methods: ['GET'])]
    public function index(): Response
    {
        // On récupère toutes les catégories
        $categories = $this->categoryRepository->findAll();
        // On compte le nombre total de catégories
        $categoryCount = $this->categoryRepository->countCategories();

        // On renvoie la vue avec les données
        return $this->render('pages/admin/category/index.html.twig', [
            'categories' => $categories,
            'categoryCount' => $categoryCount,
        ]);
    }

    // Cette route permet de créer une nouvelle catégorie
    #[Route('/admin/category/create', name: 'admin_category_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        // On crée un nombre aléatoire pour avoir une URL unique
        $date = new DateTimeImmutable();
        $timestamp = $date->getTimestamp();
        $secretKey = $timestamp *  rand(1, 10);
        
        // On crée une nouvelle instance de Category
        $category = new Category();
        // On crée le formulaire
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // On définit les dates de création et de mise à jour
            $category->setCreatedAt(new DateTimeImmutable());
            $category->setUpdatedAt(new DateTimeImmutable());
            $category->setSecretKey($secretKey);
            
            // On persiste la catégorie pour obtenir un ID
            $this->em->persist($category);
            $this->em->flush();
            
            // On récupère les fichiers image
            $imageFiles = $form->get('imageFiles')->getData();
            
            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    try {
                        // On traite chaque image
                        $photo = $this->handleImageUpload($category, $imageFile);
                        $this->em->persist($photo);
                        $category->addPhoto($photo);
                    } catch (\Exception $e) {
                        $this->addFlash('error', $e->getMessage());
                    }
                }
            }
            
            // On sauvegarde les photos
            $this->em->flush();
            $this->em->refresh($category);

            // On ajoute un message de succès et on redirige
            $this->addFlash("success", "La nouvelle catégorie a été ajoutée avec succès.");
            return $this->redirectToRoute("admin_category_index");
        }
        
        // Si le formulaire n'est pas soumis ou pas valide, on affiche le formulaire
        return $this->render('pages/admin/category/create.html.twig', [
            "form" => $form->createView()
        ]);
    }

    // Cette route permet d'éditer une catégorie existante
    #[Route('/admin/category/{id<\d+>}/edit', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Category $category, Request $request): Response {
        // On crée le formulaire d'édition
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // On met à jour la date de modification
            $category->setUpdatedAt(new DateTimeImmutable());

            // On gère les photos existantes
            if ($form->has('existingPhotos')) {
                $existingPhotos = $form->has('existingPhotos') ? $form->get('existingPhotos')->getData() : [];
                foreach ($category->getPhotos() as $photo) {
                    if (!isset($existingPhotos[$photo->getId()]) || $existingPhotos[$photo->getId()] === false) {
                        $this->removePhoto($photo);
                        $category->removePhoto($photo);
                    }
                }
            }

            // On gère les nouvelles photos
            if ($form->has('imageFiles')) {
                $imageFiles = $form->get('imageFiles')->getData();
                if ($imageFiles) {
                    foreach ($imageFiles as $imageFile) {
                        try {
                            $photo = $this->handleImageUpload($category, $imageFile);
                            $this->em->persist($photo);
                            $category->addPhoto($photo);
                        } catch (\Exception $e) {
                            $this->addFlash('error', $e->getMessage());
                        }
                    }
                }
            }

            // On sauvegarde les modifications
            $this->em->flush();
            $this->em->refresh($category);
        
            // On ajoute un message de succès et on redirige
            $this->addFlash("success", "La catégorie a été modifiée avec succès.");
            return $this->redirectToRoute("admin_category_index");
        }

        // Si le formulaire n'est pas soumis ou pas valide, on affiche le formulaire d'édition
        return $this->render("pages/admin/category/edit.html.twig", [
            "form" => $form->createView(),
            "category" => $category
        ]);
    }

    // Cette route permet de supprimer une catégorie
    #[Route('/admin/category/{id<\d+>}/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function delete(Category $category, Request $request): Response
    {
        // On vérifie le jeton CSRF pour la sécurité
        if ($this->isCsrfTokenValid("delete_category_" . $category->getId(), $request->request->get('csrf_token'))) {
            // On supprime toutes les photos de la catégorie
            foreach ($category->getPhotos() as $photo) {
                $this->removePhoto($photo);
            }
            // On supprime la catégorie
            $this->em->remove($category);
            $this->em->flush();

            $this->addFlash("success", "La catégorie a été supprimée.");
        }

        return $this->redirectToRoute("admin_category_index");
    }

    // Cette méthode privée gère l'upload d'une image
    private function handleImageUpload(Category $category, UploadedFile $imageFile): Photos
    {
        // On génère un nom de fichier sécurisé
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

        // On vérifie que la catégorie a un ID
        if (!$category->getId()) {
            throw new \Exception("La catégorie doit être persistée avant d'ajouter des photos.");
        }

        // On définit le répertoire de la catégorie
        $categoryDirectory = $this->getParameter('photos_base_directory') . '/' . $category->getId();

        try {
            // On crée le répertoire s'il n'existe pas
            if (!is_dir($categoryDirectory)) {
                mkdir($categoryDirectory, 0777, true);
            }

            // On déplace le fichier uploadé
            $imageFile->move(
                $categoryDirectory,
                $newFilename
            );
            
            // On crée une nouvelle instance de Photos
            $photo = new Photos();
            $photo->setImageName($newFilename);
            $photo->setCategory($category);
            
            // On définit le chemin relatif de l'image
            $relativePath = 'Images/Mariages/' . $category->getId() . '/' . $newFilename;
            $photo->setAdress($relativePath);
            
            return $photo;
        } catch (FileException $e) {
            throw new \Exception("Erreur lors du téléchargement de l'image : " . $e->getMessage());
        }    
    }

    // Cette méthode privée supprime une photo
    private function removePhoto($photo): void
    {
        // On crée une instance de Filesystem
        $filesystem = new Filesystem();
        
        // On construit le chemin complet du fichier
        $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $photo->getAdress();
        
        // On récupère le chemin du dossier parent
        $directoryPath = dirname($filePath);

        try {
            // On supprime le fichier s'il existe
            if ($filesystem->exists($filePath)) {
                $filesystem->remove($filePath);
            }

            // On vérifie si le dossier est vide
            if (is_dir($directoryPath) && count(glob("$directoryPath/*")) === 0) {
                // On supprime le dossier s'il est vide
                $filesystem->remove($directoryPath);
            }
        } catch (IOExceptionInterface $exception) {
            // On gère les erreurs lors de la suppression
            throw new \Exception("Erreur lors de la suppression : " . $exception->getMessage());
        }

        // On supprime l'entité Photo de la base de données
        $this->em->remove($photo);
    }

    // Cette route API renvoie la liste des images pour une catégorie ou toutes les catégories
    #[Route('/Images/Mariages/{categoryID?}', name: 'api_images')]
    public function getImages(?string $categoryId = null): JsonResponse
    {
        $finder = new Finder();
        $images = [];

        $baseDirectory = $this->getParameter('kernel.project_dir') . '/public/Images/Mariages';

        if (is_dir($baseDirectory)) {
            if ($categoryId) {
                // Si une catégorie spécifique est demandée
                $categoryPath = $baseDirectory . '/' . $categoryId;
                if (is_dir($categoryPath)) {
                    $finder->files()->in($categoryPath);
                    foreach ($finder as $file) {
                        $filename = $file->getFilename();
                        if ($filename !== '.DS_Store') {
                            $images[] = [
                                'filename' => $filename,
                                'path' => 'Images/Mariages/' . $categoryId . '/' . $filename
                            ];
                        }
                    }
                }
            } else {
                // Si aucune catégorie n'est spécifiée, on retourne toutes les catégories
                $finder->directories()->in($baseDirectory);
                foreach ($finder as $categoryDir) {
                    $categoryId = $categoryDir->getFilename();
                    $categoryImages = [];

                    $imageFinder = new Finder();
                    $imageFinder->files()->in($categoryDir->getRealPath());

                    foreach ($imageFinder as $file) {
                        $filename = $file->getFilename();
                        if ($filename !== '.DS_Store') {
                            $categoryImages[] = [
                                'filename' => $filename,
                                'path' => 'Images/Mariages/' . $categoryId . '/' . $filename
                            ];
                        }
                    }

                    if (!empty($categoryImages)) {
                        $images[$categoryId] = $categoryImages;
                    }
                }
            }
        }

        return $this->json($images);
    }

    // Cette route API renvoie les informations d'une catégorie spécifique
    #[Route('/api/category/{id}', name: 'api_category_info', methods: ['GET'])]
    public function getCategoryInfo(int $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            return $this->json(['error' => 'Category not found'], 404);
        }

        return $this->json([
            'id' => $category->getId(),
            'name' => $category->getName(),
        ]);
    }
}