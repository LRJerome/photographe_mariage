<?php
namespace App\Controller\User;


// src/Controller/ImageMariageController.php



use Symfony\Component\Finder\Finder;
use App\Repository\CategoryRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ImageMariageController extends AbstractController
{
//     public function __construct(private CategoryRepository $categoryRepository)
//     {
//     }

//     #[Route('/Images/Mariages/{categoryID?}', name: 'api_images')]
//     public function getImages(?string $categoryId = null): JsonResponse
//     {
//         $finder = new Finder();
//         $images = [];

//         $baseDirectory = $this->getParameter('kernel.project_dir') . '/public/Images/Mariages';

//         if (is_dir($baseDirectory)) {
//             if ($categoryId) {
//                 // Si une catégorie spécifique est demandée
//                 $categoryPath = $baseDirectory . '/' . $categoryId;
//                 if (is_dir($categoryPath)) {
//                     $finder->files()->in($categoryPath);
//                     foreach ($finder as $file) {
//                         $filename = $file->getFilename();
//                         if ($filename !== '.DS_Store') {
//                             $images[] = [
//                                 'filename' => $filename,
//                                 'path' => 'Images/Mariages/' . $categoryId . '/' . $filename
//                             ];
//                         }
//                     }
//                 }
//             } else {
//                 // Si aucune catégorie n'est spécifiée, retournez toutes les catégories
//                 $finder->directories()->in($baseDirectory);
//                 foreach ($finder as $categoryDir) {
//                     $categoryId = $categoryDir->getFilename();
//                     $categoryImages = [];

//                     $imageFinder = new Finder();
//                     $imageFinder->files()->in($categoryDir->getRealPath());

//                     foreach ($imageFinder as $file) {
//                         $filename = $file->getFilename();
//                         if ($filename !== '.DS_Store') {
//                             $categoryImages[] = [
//                                 'filename' => $filename,
//                                 'path' => 'Images/Mariages/' . $categoryId . '/' . $filename
//                             ];
//                         }
//                     }

//                     if (!empty($categoryImages)) {
//                         $images[$categoryId] = $categoryImages;
//                     }
//                 }
//             }
//         }

//         return $this->json($images);
//     }

//     #[Route('/api/category/{id}', name: 'api_category_info', methods: ['GET'])]
//     public function getCategoryInfo(int $id): JsonResponse
//     {
//         $category = $this->categoryRepository->find($id);

//         if (!$category) {
//             return $this->json(['error' => 'Category not found'], 404);
//         }

//         return $this->json([
//             'id' => $category->getId(),
//             'name' => $category->getName(),
//         ]);
//     }
}