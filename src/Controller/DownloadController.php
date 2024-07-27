<?php
namespace App\Controller;

use ZipArchive;
use App\Entity\Category;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

class DownloadController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/telecharger-photos/{categoryId}', name: 'telecharger_photos')]
    public function telechargerPhotos(string $categoryId): Response
    {
        $photoPaths = $this->getPhotoPaths($categoryId);

        if (empty($photoPaths)) {
            return new Response("Pas d'images à télécharger !", Response::HTTP_NOT_FOUND);
        }

        $nomMaries = $this->getNomMaries($categoryId);
        $zipFileName = 'Photos du mariage de ' . $nomMaries . '.zip';
        $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($photoPaths as $photoPath) {
                $zip->addFile($photoPath, basename($photoPath));
            }
            $zip->close();

            $response = new BinaryFileResponse($zipFilePath);
            $response->setContentDisposition('attachment', $zipFileName);

            return $response;
        }

        return new Response('Erreur lors de la création du fichier zip', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function getPhotoPaths(string $categoryId): array
    {
        $baseDirectory = $this->getParameter('kernel.project_dir') . '/public/Images/Mariages/' . $categoryId;
        $photoPaths = [];

        if (is_dir($baseDirectory)) {
            $finder = new Finder();
            $finder->files()->in($baseDirectory);

            foreach ($finder as $file) {
                if ($file->getFilename() !== '.DS_Store') {
                    $photoPaths[] = $file->getRealPath();
                }
            }
        }

        return $photoPaths;
    }

    private function getNomMaries(string $categoryId): string
    {
        $category = $this->entityManager->getRepository(Category::class)->find($categoryId);
        
        if (!$category) {
            return 'Inconnu';
        }
        
        // Supposons que l'entité Category a une méthode getNom() qui retourne le nom de la catégorie
        return $category->getName();
    }
}