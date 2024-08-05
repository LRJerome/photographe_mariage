<?php


// Ce controller gère l'envoi d'emails contenant un lien vers les photos de mariage pour une catégorie spécifique.
// Il est utilisé dans la partie admin de mon premier site Symfony pour envoyer des emails aux clients en utilisant uniquement un Btn, pour que tout soit automatisé.

namespace App\Controller\Admin\Contact;

// J'importe toutes les classes dont j'ai besoin pour ce controller
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

// Ma classe MailerController hérite de AbstractController pour avoir accès à plein de fonctionnalités Symfony
class MailerController extends AbstractController
{
    // Cette annotation définit la route pour cette action
    #[Route('/email', name: 'app_email', methods: ['POST'])]
    public function envoyerEmail(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        // Je récupère l'ID de la catégorie depuis la requête POST
        $categoryId = $request->request->get('category_id');
        
        // J'utilise l'EntityManager pour chercher la catégorie dans la base de données
        $category = $entityManager->getRepository(Category::class)->find($categoryId);
        
        // Je vérifie si la catégorie existe, sinon je renvoie une erreur 404
        if (!$category) {
            return new JsonResponse(['message' => 'Catégorie non trouvée'], 404);
        }
        
        // Je récupère l'utilisateur associé à cette catégorie
        $user = $category->getUser();
        
        // Je vérifie si l'utilisateur existe, sinon je renvoie une erreur 404
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], 404);
        }
        
        // Je récupère l'email, le prénom et le nom de l'utilisateur
        $destinataire = $user->getEmail();
        $prenom = $user->getFirstName();
        $nom = $user->getLastName();

        // Je vérifie si l'email est valide avec une fonction PHP
        if (!filter_var($destinataire, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['message' => 'Adresse email invalide'], 400);
        }

        // Je génère un lien unique pour accéder aux photos, en utilisant la clé secrète de la catégorie
        $lien = $this->generateUrl('app_mariages', ['secretKey' => $category->getSecretKey()], UrlGeneratorInterface::ABSOLUTE_URL);

        // Je crée un nouvel email en utilisant un template Twig
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

        // J'envoie l'email en utilisant le service Mailer de Symfony
        $mailer->send($email);

        // J'ajoute un message flash pour informer l'admin que l'email a été envoyé
        $this->addFlash("success", "Votre e-mail contenant le lien à bien etait envoyé.");

        // Je redirige vers la page d'index des catégories
        return $this->redirectToRoute('admin_category_index');
    }
}