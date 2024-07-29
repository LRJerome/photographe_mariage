<?php
namespace App\Controller\Admin\Category;


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
    // Constructeur avec injection de dépendances
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryRepository $categoryRepository,
        private SluggerInterface $slugger
    ) {
    }

    // Route pour afficher la liste des catégories
    #[Route('/admin/category/list', name: 'admin_category_index', methods: ['GET'])]
public function index(): Response
{
    $categories = $this->categoryRepository->findAll();
    $categoryCount = $this->categoryRepository->countCategories();

            // Rendu de la vue avec toutes les catégories
        return $this->render('pages/admin/category/index.html.twig', [
            'categories' => $categories,
        'categoryCount' => $categoryCount,
    ]);
    }

    // Route pour créer une nouvelle catégorie
    #[Route('/admin/category/create', name: 'admin_category_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        // creation d'un chiffre aleatoir pour url unique
        $date = new DateTimeImmutable();
        $timestamp = $date->getTimestamp();
        $secretKey = $timestamp *  rand(1, 10);
        
        // Création d'une nouvelle instance de Category
        $category = new Category();
        // Création du formulaire
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Définition des dates de création et de mise à jour
            $category->setCreatedAt(new DateTimeImmutable());
            $category->setUpdatedAt(new DateTimeImmutable());
            $category->setSecretKey($secretKey);
            
            // Persistance de la catégorie pour obtenir un ID
            $this->em->persist($category);
            $this->em->flush();
            
            // Récupération des fichiers image
            $imageFiles = $form->get('imageFiles')->getData();
            
            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    try {
                        // Traitement de chaque image
                        $photo = $this->handleImageUpload($category, $imageFile);
                        $this->em->persist($photo);
                        $category->addPhoto($photo);
                    } catch (\Exception $e) {
                        $this->addFlash('error', $e->getMessage());
                    }
                }
            }
            
            // Sauvegarde des photos
            $this->em->flush();
            $this->em->refresh($category);

            // Message de succès et redirection
            $this->addFlash("success", "La nouvelle catégorie a été ajoutée avec succès.");
            return $this->redirectToRoute("admin_category_index");
        }
        
        // Rendu du formulaire de création
        return $this->render('pages/admin/category/create.html.twig', [
            "form" => $form->createView()
        ]);
    }

    // Route pour éditer une catégorie existante
    #[Route('/admin/category/{id<\d+>}/edit', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Category $category, Request $request): Response {
        // Création du formulaire d'édition
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour de la date de modification
            $category->setUpdatedAt(new DateTimeImmutable());

            // Gestion des photos existantes
            if ($form->has('existingPhotos')) {
                $existingPhotos = $form->has('existingPhotos') ? $form->get('existingPhotos')->getData() : [];
                foreach ($category->getPhotos() as $photo) {
                    if (!isset($existingPhotos[$photo->getId()]) || $existingPhotos[$photo->getId()] === false) {
                        $this->removePhoto($photo);
                        $category->removePhoto($photo);
                    }
                }
            }

            // Gestion des nouvelles photos
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

            // Sauvegarde des modifications
            $this->em->flush();
            $this->em->refresh($category);
        
            // Message de succès et redirection
            $this->addFlash("success", "La catégorie a été modifiée avec succès.");
            return $this->redirectToRoute("admin_category_index");
        }
    
        // Rendu du formulaire d'édition
        return $this->render("pages/admin/category/edit.html.twig", [
            "form" => $form->createView(),
            "category" => $category
        ]);
    }

    // Route pour supprimer une catégorie
    #[Route('/admin/category/{id<\d+>}/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function delete(Category $category, Request $request): Response
    {
        // Vérification du jeton CSRF
        if ($this->isCsrfTokenValid("delete_category_" . $category->getId(), $request->request->get('csrf_token'))) {
            // Suppression de toutes les photos de la catégorie
            foreach ($category->getPhotos() as $photo) {
                $this->removePhoto($photo);
            }
            // Suppression de la catégorie
            $this->em->remove($category);
            $this->em->flush();

            $this->addFlash("success", "La catégorie a été supprimée.");
        }

        return $this->redirectToRoute("admin_category_index");
    }

    // Méthode pour gérer l'upload d'une image
    private function handleImageUpload(Category $category, UploadedFile $imageFile): Photos
    {
        // Génération d'un nom de fichier sécurisé
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

        // Vérification que la catégorie a un ID
        if (!$category->getId()) {
            throw new \Exception("La catégorie doit être persistée avant d'ajouter des photos.");
        }

        // Définition du répertoire de la catégorie
        $categoryDirectory = $this->getParameter('photos_base_directory') . '/' . $category->getId();

        try {
            // Création du répertoire s'il n'existe pas
            if (!is_dir($categoryDirectory)) {
                mkdir($categoryDirectory, 0777, true);
            }

            // Déplacement du fichier uploadé
            $imageFile->move(
                $categoryDirectory,
                $newFilename
            );
            
            // Création d'une nouvelle instance de Photos
            $photo = new Photos();
            $photo->setImageName($newFilename);
            $photo->setCategory($category);
            
            // Définition du chemin relatif de l'image
            $relativePath = 'Images/Mariages/' . $category->getId() . '/' . $newFilename;
            $photo->setAdress($relativePath);
            
            return $photo;
        } catch (FileException $e) {
            throw new \Exception("Erreur lors du téléchargement de l'image : " . $e->getMessage());
        }    
    }

    // Méthode pour supprimer une photo
    private function removePhoto($photo): void
    {
        // Création d'une instance de Filesystem
        $filesystem = new Filesystem();
        
        // Construction du chemin complet du fichier
        $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $photo->getAdress();
        
        // Récupération du chemin du dossier parent
        $directoryPath = dirname($filePath);

        try {
            // Suppression du fichier s'il existe
            if ($filesystem->exists($filePath)) {
                $filesystem->remove($filePath);
            }

            // Vérification si le dossier est vide
            if (is_dir($directoryPath) && count(glob("$directoryPath/*")) === 0) {
                // Suppression du dossier s'il est vide
                $filesystem->remove($directoryPath);
            }
        } catch (IOExceptionInterface $exception) {
            // Gestion des erreurs lors de la suppression
            throw new \Exception("Erreur lors de la suppression : " . $exception->getMessage());
        }

        // Suppression de l'entité Photo de la base de données
        $this->em->remove($photo);
    }
    // #[Route("/images/list/{secretKey<\d+>}", name: "images_list")]
    // public function listImages()
    // {
    //     $imagesDir = $this->getParameter('kernel.project_dir') . '/public/Images/Originales';
    //     $images = array_values(array_diff(scandir($imagesDir), array('..', '.')));
        
    
    //     return new JsonResponse($images);
    // }

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
                // Si aucune catégorie n'est spécifiée, retournez toutes les catégories
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


