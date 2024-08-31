<?php

// Je définis le namespace de mon controller
namespace App\Controller\Visitor;

// J'importe les classes dont j'ai besoin pour mon controller
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Je crée ma classe InscriptionController qui étend AbstractController
class Old_InscriptionController extends AbstractController
{
    // J'utilise l'attribut Route pour définir l'URL et le nom de ma route
    #[Route('/inscription', name: 'app_inscription')]
    
    // Je crée ma méthode inscription qui va gérer la page d'inscription
    public function inscription(): Response
    {        
        // Je retourne la vue Twig pour afficher ma page d'inscription
        // Le chemin du template est relatif au dossier templates/
        return $this->render('/pages/visitor/inscription.html.twig');
    }
}
