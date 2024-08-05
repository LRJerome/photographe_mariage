<?php
// Définition de l'espace de noms pour ce contrôleur
namespace App\Controller\Visitor\Authentification;

// Importation des classes nécessaires
use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

// Définition de la route de base pour toutes les méthodes de ce contrôleur
#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    // Utilisation du trait ResetPasswordControllerTrait pour ajouter des fonctionnalités de réinitialisation de mot de passe
    use ResetPasswordControllerTrait;

    // Constructeur du contrôleur avec injection de dépendances
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {
    }

    // Méthode pour afficher et traiter le formulaire de demande de réinitialisation de mot de passe
    #[Route('', name: 'visitor_authentification_forgot_password_request')]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        // Création du formulaire de demande de réinitialisation
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        // Vérification si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement de l'envoi de l'email de réinitialisation
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer,
                $translator
            );
        }

        // Affichage du formulaire de demande de réinitialisation
        return $this->render('pages/visitor/authentification/reset_password/request.html.twig', [
            'requestForm' => $form,
        ]);
    }

    // Méthode pour afficher la page de confirmation après la demande de réinitialisation
    #[Route('/check-email', name: 'visitor_authentification_forgot_check_email')]
    public function checkEmail(): Response
    {
        // Génération d'un faux token si l'utilisateur n'existe pas ou si quelqu'un accède directement à cette page
        // Cela empêche de révéler si un utilisateur a été trouvé avec l'adresse e-mail donnée ou non
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        // Affichage de la page de confirmation
        return $this->render('pages/visitor/authentification/reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    // Méthode pour valider et traiter l'URL de réinitialisation sur laquelle l'utilisateur a cliqué dans son e-mail
    #[Route('/reset/{token}', name: 'visitor_authentification_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, ?string $token = null): Response
    {
        // Vérification et stockage du token
        if ($token) {
            $this->storeTokenInSession($token);
            return $this->redirectToRoute('visitor_authentification_reset_password');
        }

        $token = $this->getTokenFromSession();

        // Vérification de l'existence du token
        if (null === $token) {
            throw $this->createNotFoundException('Aucun token de réinitialisation de mot de passe trouvé dans l\'URL ou dans la session.');
        }

        try {
            // Validation du token et récupération de l'utilisateur associé
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            // Gestion des erreurs de validation du token
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            ));

            return $this->redirectToRoute('visitor_authentification_forgot_password_request');
        }

        // Création du formulaire de changement de mot de passe
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        // Traitement du formulaire de changement de mot de passe
        if ($form->isSubmitted() && $form->isValid()) {
            // Suppression de la demande de réinitialisation
            $this->resetPasswordHelper->removeResetRequest($token);

            // Hachage et définition du nouveau mot de passe
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            // Nettoyage de la session après la réinitialisation du mot de passe
            $this->cleanSessionAfterReset();

            return $this->redirectToRoute('visitor_authentification_login');
        }

        // Affichage du formulaire de réinitialisation du mot de passe
        return $this->render('pages/visitor/authentification/reset_password/reset.html.twig', [
            'resetForm' => $form,
        ]);
    }

    // Méthode privée pour traiter l'envoi de l'e-mail de réinitialisation du mot de passe
    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): RedirectResponse
    {
        // Recherche de l'utilisateur par son adresse e-mail
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Redirection vers la page de confirmation, que l'utilisateur existe ou non
        if (!$user) {
            return $this->redirectToRoute('visitor_authentification_forgot_check_email');
        }

        try {
            // Génération du token de réinitialisation
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // Gestion des erreurs lors de la génération du token
            return $this->redirectToRoute('visitor_authentification_forgot_check_email');
        }

        // Création et envoi de l'e-mail de réinitialisation
        $email = (new TemplatedEmail())
            ->from(new Address('j-leroy@gmail.com', 'j-leroy'))
            ->to($user->getEmail())
            ->subject('Réinitialisation du mot de passe de j-leroy.fr ')
            ->htmlTemplate('pages/visitor/authentification/reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        $mailer->send($email);

        // Stockage du token dans la session pour la route de vérification d'e-mail
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('visitor_authentification_forgot_check_email');
    }
}
