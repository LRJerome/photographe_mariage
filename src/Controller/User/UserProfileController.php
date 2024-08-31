<?php
namespace App\Controller\User;



use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user/profile')]
class UserProfileController extends AbstractController
{
    
    #[Route('/', name: 'app_user_profile_show', methods: ['GET'])]
    public function show(): Response
    {
        $user = $this ->getUser();
        return $this->render('user_profile/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit', name: 'app_user_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this ->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_profile_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user_profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

}
