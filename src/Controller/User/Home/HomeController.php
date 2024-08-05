<?php

// Déclaration du namespace pour le contrôleur. Cela permet de l'organiser dans l'arborescence des fichiers.
namespace App\Controller\User\Home;

// Importation des classes nécessaires pour le fonctionnement du contrôleur.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Définition de la route de base pour ce contrôleur. Toutes les routes définies dans cette classe commenceront par '/user'.
#[Route('/user')]
class HomeController extends AbstractController
{
    // Définition de la route pour la méthode index. Cette route est accessible via '/user/home' et accepte uniquement les requêtes GET.
    #[Route('/home', name: 'user_home_index', methods:['GET'])]
    public function index(): Response
    {
        // La méthode render() génère la réponse HTTP en utilisant le template Twig spécifié.
        // Le tableau passé en deuxième argument contient les variables à transmettre au template.
        return $this->render('pages/user/home/index.html.twig', [
            // Transmission du nom du contrôleur à la vue. Cela peut être utile pour des raisons de débogage ou d'affichage.
            'controller_name' => 'HomeController',
        ]);
    }
}