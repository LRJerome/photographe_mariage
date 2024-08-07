<?php
// Je définis le namespace de mon controller. C'est important pour l'autoloading de Symfony.
namespace App\Controller\Visitor\Authentification;

// J'importe les classes dont j'ai besoin pour mon controller.
// AbstractController me donne accès à plein de méthodes utiles.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// Ma classe de controller hérite de AbstractController pour avoir accès à ses méthodes.
class LoginController extends AbstractController
{
    // Je définis ma route pour la page de connexion.
    // Le path sera l'URL, et le name me servira pour générer des liens vers cette page.
    #[Route(path: '/login', name: 'visitor_authentification_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
      
        // Je récupère l'erreur d'authentification s'il y en a une.
        // Ça me permettra d'afficher un message d'erreur si la connexion a échoué.
        $error = $authenticationUtils->getLastAuthenticationError();

        // Je récupère le dernier nom d'utilisateur entré.
        // C'est pratique pour pré-remplir le champ si l'utilisateur s'est trompé de mot de passe.
        $lastUsername = $authenticationUtils->getLastUsername();

        // Je renvoie ma vue Twig en lui passant les variables dont elle aura besoin.
        // 'last_username' et 'error' seront disponibles dans mon template.
        return $this->render('pages/visitor/authentification/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error
        ]);
    }

    // Je définis ma route pour la déconnexion.
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode est vide car Symfony gère la déconnexion automatiquement.
        // Le message d'erreur est là pour me rappeler que je n'ai pas besoin de coder quoi que ce soit ici.
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
