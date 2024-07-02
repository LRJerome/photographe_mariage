<?php
namespace App\Controller\User\Home;



use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryPublicController extends AbstractController
{
    #[Route('/category/{id}', name: 'category_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showPhotos(int $id, EntityManagerInterface $entityManager): Response
    {
        $category = $entityManager->getRepository(Category::class)->find($id);
        if (!$category) {
            throw $this->createNotFoundException('La catégorie demandée n\'existe pas');
        }
        $photos = $category->getPhotos();
        return $this->render('pages/user/category/photos.html.twig', [
            'category' => $category,
            'photos' => $photos,
        ]);
    }
}