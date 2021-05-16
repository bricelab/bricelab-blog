<?php

namespace App\Controller\Backend;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Services\FileUploaderHelper;
use App\Services\MessageFlashHelper;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/post')]
class PostController extends AbstractController
{
    private FileUploaderHelper $fileUploaderHelper;

    private SluggerInterface $slugger;

    /**
     * PostController constructor.
     * @param FileUploaderHelper $fileUploaderHelper
     * @param SluggerInterface $slugger
     */
    public function __construct(FileUploaderHelper $fileUploaderHelper, SluggerInterface $slugger)
    {
        $this->fileUploaderHelper = $fileUploaderHelper;
        $this->slugger = $slugger;
    }


    #[Route('/', name: 'post_index', methods: ['GET'])]
    public function index(Request $request, PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'paginator' => $postRepository->findLatest($request->query->get('page', 1)),
        ]);
    }

    #[Route('/new', name: 'post_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $featuredImage = $this->fileUploaderHelper->uploadPostFeaturedImage($form->get('featuredImage')->getData());

            if ($featuredImage) {
                /** @var User $user */
                $user = $this->getUser();
                $post->setFeaturedImage($featuredImage);
                $post->setAuthor($user);
                $post->setSlug($this->slugger->slug($post->getTitle()));
                $post->setPublishedAt(new DateTimeImmutable());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($post);
                $entityManager->flush();

                $this->addFlash('success', 'Votre article a été ajouté avec succès.');

                return $this->redirectToRoute('post_index');
            }
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $featuredImage = $this->fileUploaderHelper->uploadPostFeaturedImage($form->get('featuredImage')->getData(), $post->getFeaturedImage());

            if ($featuredImage) {
                $post->setFeaturedImage($featuredImage);
                $post->setSlug($this->slugger->slug($post->getTitle()));

                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', 'Votre article a été mis à jour avec succès.');

                return $this->redirectToRoute('post_index');
            }

        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'post_delete', methods: ['DELETE'])]
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $image = $post->getFeaturedImage();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();

            $this->fileUploaderHelper->deletePostFeaturedImage($image);

            $this->addFlash('success', 'Votre article a été supprimé avec succès.');
        }

        return $this->redirectToRoute('post_index');
    }
}
