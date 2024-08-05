<?php

namespace App\Controller;

// Voici toutes les classes dont j'ai besoin
use ZipArchive;
use App\Entity\Category;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

// Contrôleur pour gérer le téléchargement des photos
class DownloadController extends AbstractController
{
    // Gestionnaire d'entités pour interagir avec la base de données
    private $entityManager;

    // Constructeur pour injecter le gestionnaire d'entités
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Route pour télécharger les photos d'une catégorie spécifique
    #[Route('/telecharger-photos/{categoryId}', name: 'telecharger_photos')]
    public function telechargerPhotos(string $categoryId): Response
    {
        // Récupérer les chemins des photos pour la catégorie donnée
        $photoPaths = $this->getPhotoPaths($categoryId);

        // Si aucune photo n'est trouvée, retourner une réponse indiquant l'absence d'images
        if (empty($photoPaths)) {
            return new Response("Pas d'images à télécharger !", Response::HTTP_NOT_FOUND);
        }

        // Récupérer le nom des mariés pour nommer le fichier zip
        $nomMaries = $this->getNomMaries($categoryId);
        $zipFileName = 'Photos du mariage de ' . $nomMaries . '.zip';
        $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

        // Créer un fichier zip contenant les photos
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($photoPaths as $photoPath) {
                $zip->addFile($photoPath, basename($photoPath));
            }
            $zip->close();

            // Retourner le fichier zip en réponse
            $response = new BinaryFileResponse($zipFilePath);
            $response->setContentDisposition('attachment', $zipFileName);

            return $response;
        }

        // En cas d'erreur lors de la création du fichier zip, retourner une réponse d'erreur
        return new Response('Erreur lors de la création du fichier zip', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // Méthode pour récupérer les chemins des photos d'une catégorie
    private function getPhotoPaths(string $categoryId): array
    {
        // Chemin de base pour les photos de mariages
        $baseDirectory = $this->getParameter('kernel.project_dir') . '/public/Images/Mariages/' . $categoryId;
        $photoPaths = [];

        // Vérifier si le répertoire existe
        if (is_dir($baseDirectory)) {
            $finder = new Finder();
            $finder->files()->in($baseDirectory);

            // Ajouter les chemins des photos au tableau, en excluant les fichiers système
            foreach ($finder as $file) {
                if ($file->getFilename() !== '.DS_Store') {
                    $photoPaths[] = $file->getRealPath();
                }
            }
        }

        return $photoPaths;
    }

    // Méthode pour récupérer le nom des mariés à partir de l'ID de la catégorie
    private function getNomMaries(string $categoryId): string
    {
        // Récupérer la catégorie depuis la base de données
        $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
        
        // Si la catégorie n'existe pas, retourner "Inconnu"
        if (!$category) {
            return 'Inconnu';
        }
        
        // Supposer que l'entité Category a une méthode getName() qui retourne le nom de la catégorie
        return $category->getName();
    }
}