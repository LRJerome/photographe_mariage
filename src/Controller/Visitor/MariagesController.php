<?php
namespace App\Controller\Visitor;



use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MariagesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryRepository $categoryRepository,
    ) {
    }

    #[Route('/mariages/{secretKey}', name: 'app_mariages')]
    public function index($secretKey): Response
    {
        $category = $this->categoryRepository->findOneBy(['secretKey' => $secretKey]);

        if (!$category) {
            throw $this->createNotFoundException('La catégorie n\'existe pas.');
        }

        return $this->render('pages/visitor/mariages.html.twig', [
            'category' => $category
        ]);
    }
}
