<?php

namespace App\Controller\Frontend;

use App\Entity\Post;
use App\Repository\PostRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/', name: 'app_home')]
    public function index(Request $request, PostRepository $postRepository): Response
    {
        return $this->render('frontend/index.html.twig', [
            'posts' => $postRepository->findLatest($request->query->get('page', 1)),
        ]);
    }

    #[Route('/post/view/{slug}', name: 'app_post_view')]
    public function postView(Post $post, Request $request): Response
    {
        return $this->render('frontend/post_view.html.twig', [
            'post' => $post,
        ]);
    }
}
