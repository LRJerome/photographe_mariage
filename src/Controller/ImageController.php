<?php

// Cec controller gère les images de mon site.

// Le namespace est important pour que Symfony puisse trouver mon controller
namespace App\Controller;

// J'importe // J'importe les classes dont j'ai besoin
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

// Ma classe hérite de AbstractController pour avoir accès à plein de fonctionnalités utiles qui sont "rangées" dans  : -/vendor/symfony/framework-bundle/Controller/AbstractController.php
class ImageController extends AbstractController
{
    // Cette ligne définit la route pour la fonction ListImages
    // J'ai choisi "/images/list" comme URL et "images_list" comme nom de route
    #[Route("/images/list", name: "images_list")]
    public function listImages()
    {
        // Je récupère le chemin du dossier des images
        // 'kernel.project_dir' me donne le chemin racine de mon projet
        $imagesDir = $this->getParameter('kernel.project_dir') . '/public/Images/Originales';
        
        // Je liste tous les fichiers du dossier, en excluant "." et ".."
        // array_values me permet d'avoir un tableau
        $images = array_values(array_diff(scandir($imagesDir), array('..', '.')));

        // Je renvoie la liste des images au format JSON
        // pour pouvoir l'utiliser en JavaScript
        return new JsonResponse($images);
    }
}