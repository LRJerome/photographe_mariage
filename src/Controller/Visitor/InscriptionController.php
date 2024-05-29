<?php
namespace App\Controller\Visitor;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription')]
    public function inscription(): Response
    {        
        return $this->render('/pages/visitor/inscription.html.twig');
    }
}
