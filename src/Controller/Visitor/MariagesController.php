<?php
namespace App\Controller\Visitor;


// Importation des classes nécessaires pour le contrôleur
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Définition de la classe MariagesController qui hérite d'AbstractController
class MariagesController extends AbstractController
{
    // Constructeur pour injecter les dépendances nécessaires
    public function __construct(
        private EntityManagerInterface $em, // Interface pour la gestion des entités
        private CategoryRepository $categoryRepository, // Repository pour accéder aux catégories
    ) {
    }

    // Annotation pour définir la route et le nom de la route
    #[Route('/mariages/{secretKey}', name: 'app_mariages')]
    public function index($secretKey): Response
    {
        // Recherche de la catégorie en fonction de la clé secrète
        $category = $this->categoryRepository->findOneBy(['secretKey' => $secretKey]);

        // Si la catégorie n'existe pas, lancer une exception pour indiquer que la catégorie n'a pas été trouvée
        if (!$category) {
            throw $this->createNotFoundException('La catégorie n\'existe pas.');
        }

        // Rendu de la vue Twig avec la catégorie trouvée
        return $this->render('pages/visitor/mariages.html.twig', [
            'category' => $category
        ]);
    }
}
