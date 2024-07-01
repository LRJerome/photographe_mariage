<?php

// src/Controller/ImageController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    #[Route("/images/list", name: "images_list")]
    public function listImages()
    {
        $imagesDir = $this->getParameter('kernel.project_dir') . '/public/Images/Originales';
        $images = array_values(array_diff(scandir($imagesDir), array('..', '.')));

    
        return new JsonResponse($images);
    }
}