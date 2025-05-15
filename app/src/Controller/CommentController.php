<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Tweet;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class CommentController extends AbstractController
{
    #[Route('/api/comments', name: 'api_comments_list', methods: ['GET'])]
    public function list(CommentRepository $commentRepository): JsonResponse
    {
        $comments = $commentRepository->findAll();
        $data = array_map(fn(Comment $comment) => [
            'id' => $comment->getId(),
            'date' => $comment->getDate()->format('Y-m-d H:i:s'),
            'contenu' => $comment->getContenu(),
            'tweet' => [
                'id' => $comment->getTweet()->getId(),
                'content' => $comment->getTweet()->getContenu(),
                'date' => $comment->getTweet()->getDate()->format('Y-m-d H:i:s'),
                'likes' => count($comment->getTweet()->getLikes()),
            ],            
            'user' => [
                'id' => $comment->getUser()->getId(),
                'pseudo' => $comment->getUser()->getPseudo(),
                'email' => $comment->getUser()->getEmail(),
                'avatar' => $comment->getUser()->getAvatar(),
                'date_creation' => $comment->getUser()->getDateCreation()->format('Y-m-d H:i:s'),
            ],
        ], $comments);
        return $this->json($data);
    }

    #[Route('/api/comments/{id}', name: 'api_comments_show', methods: ['GET'])]
    public function show(Comment $comment): JsonResponse
    {
        $data = [
            'id' => $comment->getId(),
            'date' => $comment->getDate()->format('Y-m-d H:i:s'),
            'contenu' => $comment->getContenu(),
            'tweet' => [
                'id' => $comment->getTweet()->getId(),
                'content' => $comment->getTweet()->getContenu(),
                'date' => $comment->getTweet()->getDate()->format('Y-m-d H:i:s'),
                'likes' => count($comment->getTweet()->getLikes()),
            ],            
            'user' => [
                'id' => $comment->getUser()->getId(),
                'pseudo' => $comment->getUser()->getPseudo(),
                'email' => $comment->getUser()->getEmail(),
                'avatar' => $comment->getUser()->getAvatar(),
                'date_creation' => $comment->getUser()->getDateCreation()->format('Y-m-d H:i:s'),
            ],
        ];

        return $this->json($data);
    }

    #[Route('/api/tweets/{id}/comments', name: 'api_comments_by_tweet', methods: ['GET'])]
    public function showByTweet(Tweet $tweet): JsonResponse
    {
        $data = [
            'id' => $tweet->getId(),
            'content' => $tweet->getContenu(),
            'date' => $tweet->getDate()->format('Y-m-d H:i:s'),
            'comments' => array_map(fn(Comment $comment) => [
                'id' => $comment->getId(),
                'contenu' => $comment->getContenu(),
                'date' => $comment->getDate()->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => $comment->getUser()->getId(),
                    'pseudo' => $comment->getUser()->getPseudo(),
                    'email' => $comment->getUser()->getEmail(),
                    'avatar' => $comment->getUser()->getAvatar(),
                    'date_creation' => $comment->getUser()->getDateCreation()->format('Y-m-d H:i:s'),
                ],
            ], $tweet->getComments()->toArray()),
            'user' => [
                'id' => $tweet->getUser()->getId(),
                'pseudo' => $tweet->getUser()->getPseudo(),
                'email' => $tweet->getUser()->getEmail(),
                'avatar' => $tweet->getUser()->getAvatar(),
                'date_creation' => $tweet->getUser()->getDateCreation()->format('Y-m-d H:i:s'),
            ], $tweet->getUser(),
        ];

        return $this->json($data);
    }

    #[Route('/api/comments', name: 'api_comments_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $comment = new Comment();
        $comment->setDate(new \DateTime());
        $comment->setContenu($data['contenu']);
        $comment->setTweet($entityManager->getReference('App\Entity\Tweet', $data['tweet_id']));
        
        // Récupère l'utilisateur
        $user = $this->getUser();
        $comment->setUser($user);
        
        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->json(['message' => 'Commentaire créé avec succès'], 201);
    }

    #[Route('/api/comments/{id}', name: 'api_comments_delete', methods: ['DELETE'])]
    public function delete(Comment $comment, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->json(['message' => 'Commentaire supprimé avec succès']);
    }
}