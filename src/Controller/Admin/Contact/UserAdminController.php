<?php

// Ce controller gère les fonctionnalités d'administration des utilisateurs,
// notamment l'affichage de la liste des utilisateurs et leur suppression dans l'onglet :"Les utilisateurs / contacts".

namespace App\Controller\Admin\Contact;

// Importation des classes nécessaires
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserAdminController extends AbstractController
{
    // Cette méthode affiche la liste des utilisateurs et des contacts
    #[Route('/admin/users', name: 'admin_user_list')]
    public function list(UserRepository $userRepository, ContactRepository $contactRepository): Response
    {
        // Je récupère tous les utilisateurs avec leur date de création
        $users = $userRepository->findAllWithCreationDate();
        
        // Je récupère les contacts avec des emails uniques
        $contacts = $contactRepository->findUniqueEmailContacts();

        // J'envoie ces données à ma vue Twig
        return $this->render('pages/admin/contact/list.html.twig', [
            'users' => $users,
            'contacts' => $contacts,
        ]);
    }

    // Cette méthode gère la suppression d'un utilisateur
    #[Route('/admin/users/delete/{id}', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Je vérifie si le token CSRF est valide pour sécuriser la suppression
        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->request->get('_token'))) {
            // Si le token est valide, je supprime l'utilisateur
            $entityManager->remove($user);
            $entityManager->flush();

            // J'ajoute un message flash pour informer que la suppression a réussi
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } else {
            // Si le token n'est pas valide, j'ajoute un message d'erreur
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        // Je redirige vers la liste des utilisateurs après la suppression
        return $this->redirectToRoute('admin_user_list');
    }
}