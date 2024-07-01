<?php
namespace App\Controller\Admin\Category;

use App\Entity\Photos;
use DateTimeImmutable;
use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin')]
class CategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryRepository $categoryRepository,
        private SluggerInterface $slugger
    ) {
    }

    #[Route('/category/list', name: 'admin_category_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/category/index.html.twig', [
            "categories" => $this->categoryRepository->findAll()
        ]);
    }

    #[Route('/category/create', name: 'admin_category_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $category = new Category();
        
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $category->setCreatedAt(new DateTimeImmutable());
            $category->setUpdatedAt(new DateTimeImmutable());
            
            $imageFiles = $form->get('imageFiles')->getData();
            
            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $photo = $this->handleImageUpload($category, $imageFile);
                    $this->em->persist($photo);
                    $category->addPhoto($photo);
                }
            }
            
            $this->em->persist($category);
            $this->em->flush();
            
            $this->em->refresh($category);

            $this->addFlash("success", "La nouvelle catégorie a été ajoutée avec succès.");
            
            return $this->redirectToRoute("admin_category_index");
        }
        
        return $this->render('pages/admin/category/create.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route('/category/{id<\d+>}/edit', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Category $category, Request $request): Response {
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $category->setUpdatedAt(new DateTimeImmutable());
        //    test lundi----------------
        // Gérer la suppression des photos existantes
        if ($form->has('existingPhotos')) {
            $existingPhotos = $form->has('existingPhotos') ? $form->get('existingPhotos')->getData() : [];
    foreach ($category->getPhotos() as $photo) {
        if (!isset($existingPhotos[$photo->getId()]) || $existingPhotos[$photo->getId()] === false) {
            $category->removePhoto($photo);
            $this->em->remove($photo);
        }
    }
        }
        
        // Gérer l'ajout de nouvelles photos
        if ($form->has('imageFiles')) {
            $imageFiles = $form->get('imageFiles')->getData();
            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    try {
                        $photo = $this->handleImageUpload($category, $imageFile);
                        $this->em->persist($photo);
                        $category->addPhoto($photo);
                    } catch (\Exception $e) {
                        $this->addFlash('error', "Erreur lors du téléchargement d'une image : " . $e->getMessage());
                    }
                }
            }
        }
        
        $this->em->flush();
        $this->em->refresh($category);
        
        $this->addFlash("success", "La catégorie a été modifiée avec succès.");
        
        return $this->redirectToRoute("admin_category_index");
    }
    
    return $this->render("pages/admin/category/edit.html.twig", [
        "form" => $form->createView(),
        "category" => $category
    ]);
}
        
        
        
        
        
        
        
        
        
        //    test lundi----------------
           
            /* 

            $imageFiles = $form->get('imageFiles')->getData();
            
            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    $photo = $this->handleImageUpload($category, $imageFile);
                    $this->em->persist($photo);
                    $category->addPhoto($photo);
                }
            }
            // rajout qui bloque....
             
    // Gérer la suppression des photos existantes
    $existingPhotos = $form->get('existingPhotos')->getData();
    foreach ($category->getPhotos() as $photo) {
        if (!isset($existingPhotos[$photo->getId()]) || !$existingPhotos[$photo->getId()]) {
            $category->removePhoto($photo);
            $this->em->remove($photo);
        }
    }
    
    // Gérer l'ajout de nouvelles photos
    $imageFiles = $form->get('imageFiles')->getData();
    if ($imageFiles) {
        foreach ($imageFiles as $imageFile) {
            $photo = $this->handleImageUpload($category, $imageFile);
            $this->em->persist($photo);
            $category->addPhoto($photo);
        }
    }
    
            // rajout qui bloque....
            $this->em->flush();

            $this->em->refresh($category);
            
            $this->addFlash("success", "La catégorie a été modifiée avec succès.");
            
            return $this->redirectToRoute("admin_category_index");
        }
        
        return $this->render("pages/admin/category/edit.html.twig", [
            "form" => $form->createView(),
            "category" => $category
        ]);
    }
*/
    #[Route('/category/{id<\d+>}/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function delete(Category $category, Request $request): Response
    {
        if ($this->isCsrfTokenValid("delete_category_" . $category->getId(), $request->request->get('csrf_token'))) {
            $this->em->remove($category);
            $this->em->flush();

            $this->addFlash("success", "La catégorie a été supprimée.");
        }

        return $this->redirectToRoute("admin_category_index");
    }

    private function handleImageUpload(Category $category, UploadedFile $imageFile): Photos
    {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

        try {
            $imageFile->move(
                $this->getParameter('photos_directory'),
                $newFilename
            );
            
            $photo = new Photos();
            $photo->setImageName($newFilename);
            $photo->setCategory($category);
            $category->addPhoto($photo);
            
            return $photo;
        } catch (FileException $e) {
            // Gérer l'exception
            // Vous pourriez vouloir lancer une exception ici ou retourner null
            throw new \Exception("Erreur lors du téléchargement de l'image : " . $e->getMessage());
        }    
    }
}