<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Tweet;
use App\Repository\TweetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class TweetController extends AbstractController
{
    #[Route('/api/tweets', name: 'api_tweets_list', methods: ['GET'])]
    public function list(TweetRepository $tweetRepository): JsonResponse
    {
        $tweets = $tweetRepository->findAll();
        $data = array_map(fn(Tweet $tweet) => [
            'id' => $tweet->getId(),
            'content' => $tweet->getContenu(),
            'author' => [
                'id' => $tweet->getUser()->getId(),
                'pseudo' => $tweet->getUser()->getPseudo(),
                'email' => $tweet->getUser()->getEmail(),
                'avatar' => $tweet->getUser()->getAvatar(),
                'date_creation' => $tweet->getUser()->getDateCreation()->format('Y-m-d H:i:s'),
            ],
            'date' => $tweet->getDate()->format('Y-m-d H:i:s'),
            'likes' => count($tweet->getLikes()),
            'comments' => [
                'count' => count($tweet->getComments()),
                'comments' => array_map(fn($comment) => [
                    'id' => $comment->getId(),
                    'content' => $comment->getContenu(),
                    'author' => [
                        'id' => $comment->getUser()->getId(),
                        'pseudo' => $comment->getUser()->getPseudo(),
                        'email' => $comment->getUser()->getEmail(),
                        'avatar' => $comment->getUser()->getAvatar(),
                        'date_creation' => $comment->getUser()->getDateCreation()->format('Y-m-d H:i:s'),
                    ],
                    'date' => $comment->getDate()->format('Y-m-d H:i:s'),
                ], $tweet->getComments()->toArray())
            ]
        ], $tweets);

        return $this->json($data);
    }

    #[Route('/api/tweets/{id}', name: 'api_tweet_show', methods: ['GET'])]
    public function show(Tweet $tweet): JsonResponse
    {
        $data = [
            'id' => $tweet->getId(),
            'content' => $tweet->getContenu(),
            'author' => [
                'id' => $tweet->getUser()->getId(),
                'pseudo' => $tweet->getUser()->getPseudo(),
                'email' => $tweet->getUser()->getEmail(),
                'avatar' => $tweet->getUser()->getAvatar(),
                'date_creation' => $tweet->getUser()->getDateCreation()->format('Y-m-d H:i:s'),
            ],
            'date' => $tweet->getDate()->format('Y-m-d H:i:s'),
            'likes' => count($tweet->getLikes()),
            'comments' => [
                'count' => count($tweet->getComments()),
                'comments' => array_map(fn($comment) => [
                    'id' => $comment->getId(),
                    'content' => $comment->getContenu(),
                    'author' => [
                        'id' => $comment->getUser()->getId(),
                        'pseudo' => $comment->getUser()->getPseudo(),
                        'email' => $comment->getUser()->getEmail(),
                        'avatar' => $comment->getUser()->getAvatar(),
                        'date_creation' => $comment->getUser()->getDateCreation()->format('Y-m-d H:i:s'),
                    ],
                    'date' => $comment->getDate()->format('Y-m-d H:i:s'),
                ], $tweet->getComments()->toArray())
            ]
        ];

        return $this->json($data);
    }

    #[Route('/api/tweets', name: 'api_tweets_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $tweet = new Tweet();
        $tweet->setContenu($data['content']);
        $tweet->setDate(new \DateTime());

        // $user = $this->getUser(); // Récupère l'utilisateur connecté
        // $tweet->setUser($user);

        // Test avec un utilisateur par défaut en attendant l'authentification
        // Décommenter la ligne ci-dessus et commenter celle ci-dessous lorsque 
        // l'authentification sera en place
        // Crée un utilisateur fictif
        $defaultUser = new Users();
        $defaultUser->setPseudo('DefaultUser');
        $defaultUser->setEmail('default@example.com');
        $defaultUser->setAvatar('default-avatar.png');
        $defaultUser->setDateCreation(new \DateTime());
        $defaultUser->setMdp('password'); // Ne pas stocker de mot de passe en clair dans une vraie application

        $entityManager->persist($defaultUser);
        $tweet->setUser($defaultUser);

        $entityManager->persist($tweet);
        $entityManager->flush();

        return $this->json(['message' => 'Tweet créé avec succès'], 201);
    }

    #[Route('/api/tweets/{id}', name: 'api_tweets_update', methods: ['PUT'])]
    public function update(Request $request, Tweet $tweet, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $tweet->setContenu($data['content'] ?? $tweet->getContenu());
        $entityManager->flush();

        return $this->json(['message' => 'Tweet mis à jour avec succès']);
    }

    #[Route('/api/tweets/{id}', name: 'api_tweets_delete', methods: ['DELETE'])]
    public function delete(Tweet $tweet, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($tweet);
        $entityManager->flush();

        return $this->json(['message' => 'Tweet supprimé avec succès']);
    }

}
