<?php

namespace App\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/dashboard')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard_index')]
    public function index(): Response
    {
        return $this->render('backend/dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
}
