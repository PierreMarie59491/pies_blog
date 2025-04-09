<?php

// src/Controller/PostController.php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/post')]
class PostController extends AbstractController
{
    // Affichage de la liste des posts
    #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    // Création d'un nouveau post
    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On associe l'utilisateur connecté comme auteur du post
            $user = $this->getUser();
            if ($user) {
                // Utilisation de l'ID ou pseudo de l'utilisateur pour l'attribuer comme auteur
                $post->setUser($user->getPseudo());  // Utiliser getPseudo() ou getId() selon ta préférence
            }

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_index');
        }

        return $this->render('post/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Affichage d'un post spécifique
    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    // Édition d'un post (uniquement pour l'utilisateur connecté ou un admin)
   // src/Controller/PostController.php

#[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
{
    // Vérifier si l'utilisateur connecté est celui qui a créé le post ou un administrateur
    if ($post->getUser() !== $this->getUser()->getPseudo() && !$this->isGranted('ROLE_ADMIN')) {
        throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce post.');
    }

    $form = $this->createForm(PostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('app_post_index');
    }

    return $this->render('post/edit.html.twig', [
        'form' => $form->createView(),
        'post' => $post,
    ]);
}


    // Suppression d'un post
    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur a le droit de supprimer ce post
        if ($this->getUser()->getPseudo() !== $post->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à la suppression de ce post.');
        }

        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index');
    }
}
