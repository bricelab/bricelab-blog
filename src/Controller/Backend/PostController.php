<?php

namespace App\Controller\Backend;

use App\Entity\Post;
use App\Entity\User;
use App\Form\CreatePostType;
use App\Form\UpdatePostType;
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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\UX\Dropzone\Form\DropzoneType;

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
        return $this->render('backend/post/index.html.twig', [
            'paginator' => $postRepository->findPaginatedListe($request->query->get('page', 1)),
        ]);
    }

    #[Route('/new', name: 'post_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(CreatePostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $featuredImage = $this->fileUploaderHelper->uploadPostFeaturedImage($form->get('featuredImage')->getData());

            if ($featuredImage) {
                /** @var User $user */
                $user = $this->getUser();
                $post->setFeaturedImage($featuredImage);
                $post->setAuthor($user);
                $post->setSlug($this->slugger->slug($post->getTitle()));

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($post);
                $entityManager->flush();

                $this->addFlash('success', 'Votre article a été ajouté avec succès.');

                return $this->redirectToRoute('post_index');
            }
        }

        return $this->render('backend/post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/details', name: 'post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('backend/post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(UpdatePostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setSlug($this->slugger->slug($post->getTitle()));

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Votre article a été mis à jour avec succès.');

            return $this->redirectToRoute('post_index');

        }

        return $this->render('backend/post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'post_delete', methods: ['DELETE'])]
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

    #[Route('/publish', name: 'post_publish', methods: ['POST'])]
    public function publish(Request $request, PostRepository $postRepository): Response
    {
        ["postId" => $postId, "status" => $status] = json_decode($request->getContent(), true);

        $post = $postRepository->find($postId);

        if ($post) {
            if ($status) {
                $post->setStatus(Post::POST_STATUS_PUBLISHED);
                $post->setPublishedAt(new DateTimeImmutable());
            } else {
                $post->setStatus(Post::POST_STATUS_DRAFT);
                $post->setPublishedAt(null);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->json('Votre article a été publié avec succès.');
        }

        return $this->json('Aucun article trouvé', Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}/edit-featured-image', name: 'post_edit_featured_image', methods: ['GET', 'POST'])]
    public function editFeaturedImage(Request $request, Post $post): Response
    {
        $form = $this->createFormBuilder()
            ->add('featuredImage', DropzoneType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'mb-3 mt-3',
                    'placeholder' => 'Glissez & déposez ou Cliquez pour sélectionner',
                ],
                'label_attr' => [
                    'class' => 'mt-3 fw-bold',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
                'mapped' => false,
            ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $featuredImage = $this->fileUploaderHelper->uploadPostFeaturedImage($form->get('featuredImage')->getData(), $post->getFeaturedImage());

            if ($featuredImage) {
                $post->setFeaturedImage($featuredImage);

                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', 'Votre article a été mis à jour avec succès.');

                return $this->redirectToRoute('post_index');
            }
        }

        return $this->render('backend/post/edit_featured_image.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
}
