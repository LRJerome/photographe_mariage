<?php
namespace App\Controller\User\Home;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/user/home', name: 'user_home_index', methods:['GET'])]
    public function index(): Response
    {
        return $this->render('pages/user/home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
