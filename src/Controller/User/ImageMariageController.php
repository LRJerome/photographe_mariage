<?php
namespace App\Controller\User;


// src/Controller/ImageMariageController.php



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Finder\Finder;

class ImageMariageController extends AbstractController
{
    #[Route('/Images/Mariages', name: 'api_images')]
    public function getImages(): JsonResponse
    {
            $finder = new Finder();
        $images = [];

        $directory = $this->getParameter('kernel.project_dir') . '/public/Images/Mariages';

        if (is_dir($directory)) {
            $finder->files()->in($directory);

            foreach ($finder as $file) {
                $filename = $file->getFilename();
                if ($filename !== '.DS_Store') {
                    $images[] = $filename;
                }
            }
        }

        return $this->json($images);
    }
}