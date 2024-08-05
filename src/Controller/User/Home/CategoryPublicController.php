<?php

// Je définis le namespace de mon contrôleur. C'est important pour l'autoloading de Symfony.
namespace App\Controller\User\Home;

// J'importe les classes dont j'ai besoin dans mon contrôleur.
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Ma classe de contrôleur hérite de AbstractController, ce qui me donne accès à plein de méthodes utiles.
class CategoryPublicController extends AbstractController
{
    // Cette annotation définit la route pour cette action. 
    // '/category/{id}' est l'URL, 'category_show' est le nom de la route.
    // 'methods' spécifie que cette route ne répond qu'aux requêtes GET.
    // 'requirements' s'assure que l'id est bien un nombre.
    #[Route('/category/{id}', name: 'category_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    
    // Ma méthode pour afficher les photos d'une catégorie.
    // Elle prend en paramètre l'id de la catégorie et l'EntityManager de Doctrine.
    public function showPhotos(int $id, EntityManagerInterface $entityManager): Response
    {
        // Je cherche la catégorie dans la base de données avec l'id fourni.
        $category = $entityManager->getRepository(Category::class)->find($id);
        
        // Si la catégorie n'existe pas, je lance une exception 404.
        if (!$category) {
            throw $this->createNotFoundException('La catégorie demandée n\'existe pas');
        }
        
        // Je récupère toutes les photos associées à cette catégorie.
        $photos = $category->getPhotos();
        
        // Je rends le template Twig en lui passant la catégorie et les photos.
        // C'est ce template qui va générer le HTML de ma page.
        return $this->render('pages/user/category/photos.html.twig', [
            'category' => $category,
            'photos' => $photos,
        ]);
    }
}