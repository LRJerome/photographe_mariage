<?php
namespace App\Controller\Visitor;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MariagesController extends AbstractController
{
    #[Route('/mariages', name: 'app_mariages')]
    public function index(): Response
    {
        return $this->render('pages/visitor/mariages.html.twig');
    }
}
