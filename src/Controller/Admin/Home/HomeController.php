<?php
namespace App\Controller\Admin\Home;



use App\Service\StatisticsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class HomeController extends AbstractController
{
    #[Route('/home/', name: 'admin_home_index',methods:['GET'])]
    public function index(StatisticsService $statisticsService): Response
    {
        return $this->render('pages/admin/home/index.html.twig', [
            'categoryCount' => $statisticsService->getCategoryCount(),
            'contactMessageCount' => $statisticsService->getContactMessageCount(),
            'userCount' => $statisticsService->getUserCount(),
            'messageCount' => $statisticsService->getMessageCount(),
        ]);
}
}
