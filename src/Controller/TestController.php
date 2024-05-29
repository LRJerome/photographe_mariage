<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Loader\Configurator\test;

class TestController extends AbstractController
{
    #[Route('/', name: 'app_test')]
    public function index(): Response
    {        
        return $this->render('/pages/index.html.twig');
    }

    #[Route('/exemples', name: 'app_exemples')]
    public function exemples(): Response
    {        
        return $this->render('/pages/exemples.html.twig');
    }
    #[Route('/inscription', name: 'app_inscription')]
    public function inscription(): Response
    {        
        return $this->render('/pages/inscription.html.twig');
    }
    #[Route('/cgv', name: 'app_mentionslegales')]
    public function mentionslegales(): Response
    {        
        return $this->render('/pages/mentionslegales.html.twig');
    }
    
}

