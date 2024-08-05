<?php

// Ce controller gère les opérations liées aux contacts dans la partie admin du site
namespace App\Controller\Admin\Contact;

// Importation des classes nécessaires
use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Définition de la classe ContactController qui étend AbstractController
class ContactController extends AbstractController
{
    // Route pour afficher la liste des contacts
    #[Route('/admin/contact/list', name: 'admin_contact_index', methods:['GET'])]
    public function index(ContactRepository $contactRepository): Response
    {
        // Récupération de tous les contacts depuis la base de données
        $contacts = $contactRepository->findAll();
        
        // Rendu de la vue avec les contacts récupérés
        return $this->render('pages/admin/contact/index.html.twig', [
            'contacts' => $contacts
        ]);
    }

    // Route pour supprimer un contact
    #[Route('/admin/contact/{id<\d+>}/delete', name: 'admin_contact_delete', methods:['POST'])]
    public function delete(Contact $contact, Request $request, EntityManagerInterface $em): Response
    {
        // Vérification du jeton CSRF pour la sécurité
        if ($this->isCsrfTokenValid("delete_contact_" . $contact->getId(), $request->request->get('csrf_token'))) {
            // Suppression du contact de la base de données
            $em->remove($contact);
            $em->flush();

            // Ajout d'un message flash pour informer l'utilisateur
            $this->addFlash("success", "Le message a bien été supprimée.");
        }

        // Redirection vers la liste des contacts après la suppression
        return $this->redirectToRoute("admin_contact_index");
    }
}
