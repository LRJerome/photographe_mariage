<?php
// Ce controller gère la page d'accueil de l'administration de mon site.
// Il affiche des statistiques générales sur le contenu du site.

// Définition du namespace pour ce controller
namespace App\Controller\Admin\Home;

// Importation des classes nécessaires
use App\Service\StatisticsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Cette annotation indique que toutes les routes de ce controller commenceront par '/admin'
#[Route('/admin')]
class HomeController extends AbstractController
{
    // Cette méthode gère la page d'accueil de l'admin
    // L'annotation Route définit l'URL et le nom de la route
    // 'methods:['GET']' spécifie que cette route ne répond qu'aux requêtes GET
    #[Route('/home/', name: 'admin_home_index', methods:['GET'])]
    public function index(StatisticsService $statisticsService): Response
    {
        // Ici, on utilise l'injection de dépendances pour obtenir le service StatisticsService
        // Ce service nous permet de récupérer différentes statistiques

        // On utilise la méthode render pour afficher un template Twig
        // Le deuxième argument est un tableau associatif qui passe des variables au template
        return $this->render('pages/admin/home/index.html.twig', [
            // On récupère le nombre de catégories
            'categoryCount' => $statisticsService->getCategoryCount(),
            // On récupère le nombre de messages de contact
            'contactMessageCount' => $statisticsService->getContactMessageCount(),
            // On récupère le nombre d'utilisateurs
            'userCount' => $statisticsService->getUserCount(),
            // On récupère le nombre total de messages
            'messageCount' => $statisticsService->getMessageCount(),
            // On récupère le nombre de contacts uniques
            'uniqueContactsCount' => $statisticsService->getUniqueContactsCount(),
        ]);
    }
}
