<?php


namespace App\Controller\Admin\Contact;

use App\Entity\User;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MailerController extends AbstractController
{
    #[Route('/email', name: 'app_email', methods: ['POST'])]
    public function envoyerEmail(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        // Récupère l'ID de la catégorie depuis la requête
        $categoryId = $request->request->get('category_id');
        
        // Récupère la catégorie à partir de l'ID
        $category = $entityManager->getRepository(Category::class)->find($categoryId);
        
        // Vérifie si la catégorie existe
        if (!$category) {
            return new JsonResponse(['message' => 'Catégorie non trouvée'], 404);
        }
        
        // Récupère l'utilisateur associé à la catégorie
        $user = $category->getUser();
        
        // Vérifie si l'utilisateur existe
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], 404);
        }
        
        $destinataire = $user->getEmail();
        $prenom = $user->getFirstName(); // Assurez-vous que ces méthodes existent dans votre entité User
        $nom = $user->getLastName();

        // Vérifie si l'email est valide
        if (!filter_var($destinataire, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['message' => 'Adresse email invalide'], 400);
        }

        // Génère le lien avec la secretKey de la catégorie
        $lien = $this->generateUrl('app_mariages', ['secretKey' => $category->getSecretKey()], UrlGeneratorInterface::ABSOLUTE_URL);

        // Crée un nouvel email avec un template
        $email = (new TemplatedEmail())
            ->from('j-leroy@gmail.fr')
            ->to($destinataire)
            ->subject('Voici les photos de votre mariage!')
            ->htmlTemplate('email/mailerLien.html.twig')
            ->context([
                'prenom' => $prenom,
                'nom' => $nom,
                'lien' => $lien,
            ]);

        // Envoie l'email
        $mailer->send($email);

        $this->addFlash("success", "Votre e-mail contenant le lien à bien etait envoyé.");

        // Redirige vers la page d'index des catégories
        return $this->redirectToRoute('admin_category_index');
    }
}