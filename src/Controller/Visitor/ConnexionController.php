<?php

// Namespace de mon controller. C'est important pour l'organisation de mon code.
namespace App\Controller\Visitor;

// J'importe les classes dont j'ai besoin pour mon controller.
// AbstractController me donne accès à plein de fonctionnalités utiles de Symfony.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// Response me permet de renvoyer une réponse HTTP.
use Symfony\Component\HttpFoundation\Response;
// Route me permet de définir l'URL pour accéder à ma page.
use Symfony\Component\Routing\Attribute\Route;

// Ma classe ConnexionController hérite de AbstractController.
class ConnexionController extends AbstractController
{
    // Cette annotation définit la route pour ma page de connexion.
    // L'URL sera '/connexion' et le nom de la route 'app_connexion'.
    #[Route('/connexion', name: 'app_connexion')]
    public function connexion(): Response
    {
        // Cette méthode renvoie la vue Twig pour ma page de connexion.
        // Le fichier Twig se trouve dans templates/pages/visitor/connexion.html.twig.
        return $this->render('pages/visitor/connexion.html.twig');
    }
}
