<?php

// Je définis le namespace de mon controller
namespace App\Controller\Visitor;

// Importation des classes nécessaires pour le contrôleur
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Définition de la classe ExemplesController qui hérite de AbstractController
class ExemplesController extends AbstractController
{
    // Annotation de la route pour l'URL '/exemples' avec le nom de la route 'app_exemples'
    #[Route('/exemples', name: 'app_exemples')]
    public function exemples(): Response
    {
        // Rendu du template Twig situé dans 'templates/pages/visitor/exemples.html.twig'
        return $this->render('/pages/visitor/exemples.html.twig');
    }
}