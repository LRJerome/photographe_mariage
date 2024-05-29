<?php
namespace App\Controller\Visitor;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExemplesController extends AbstractController
{
    #[Route('/exemples', name: 'app_exemples')]
    public function exemples(): Response
    {        
        return $this->render('/pages/visitor/exemples.html.twig');
    }
}
