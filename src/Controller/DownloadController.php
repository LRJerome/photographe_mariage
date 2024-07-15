<?php
namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;
use Symfony\Component\Finder\Finder;

class DownloadController extends AbstractController
{
    #[Route('/telecharger-photos/{categoryId}', name: 'telecharger_photos')]
    public function telechargerPhotos(string $categoryId): Response
    {
        $zipFileName = 'photos_mariage_' . $categoryId . '.zip';
        $zipFilePath = sys_get_temp_dir() . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $photoPaths = $this->getPhotoPaths($categoryId);

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
}