<?php

namespace App\Controller\Visitor;

// Voici toutes les classes dont j'ai besoin
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Déclaration de la classe WelcomeController qui hérite de AbstractController
class WelcomeController extends AbstractController
{
    // Annotation de route pour définir l'URL de la page d'accueil et son nom de route
    #[Route('/', name: 'app_welcome')]
    public function index(): Response
    {
        // Retourne la vue Twig située dans /pages/visitor/index.html.twig
        return $this->render('/pages/visitor/index.html.twig');
    }
}
