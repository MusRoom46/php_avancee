<?php

namespace App\Controller;

use App\Entity\Likes;
use App\Entity\Users;
use App\Entity\Tweet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class LikesController extends AbstractController
{
    #[Route('/api/tweets/{id}/likes', name: 'api_tweet_likes', methods: ['GET'])]
    public function getLikes(Tweet $tweet): JsonResponse
    {
        // Récupère les likes du tweet
        $likes = $tweet->getLikes();

        // Formate les données des likes
        $data = array_map(fn(Likes $like) => [
            'id' => $like->getId(),
            'user' => [
                'id' => $like->getUser()->getId(),
                'pseudo' => $like->getUser()->getPseudo(),
                'email' => $like->getUser()->getEmail(),
                'avatar' => $like->getUser()->getAvatar(),
                'date_creation' => $like->getUser()->getDateCreation()->format('Y-m-d H:i:s'),
            ],
            'tweet' => [
                'id' => $like->getTweet()->getId(),
                'content' => $like->getTweet()->getContenu(),
                'date' => $like->getTweet()->getDate()->format('Y-m-d H:i:s'),
            ],
            'date' => $like->getDate()->format('Y-m-d H:i:s'),
        ], $likes->toArray());

        return $this->json($data);
    }

    #[Route('/api/tweets/{id}/like', name: 'api_tweet_like', methods: ['POST'])]
    public function like(Tweet $tweet, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Récupère l'utilisateur
        $user = this.getUser();

        // Vérifie si l'utilisateur a déjà liké ce tweet
        $existingLike = null;
        foreach ($tweet->getLikes() as $like) {
            if ($like->getUser()->getId() === $user->getId()) {
                $existingLike = $like;
                break;
            }
        }

        if ($existingLike) {
            // Si un like existe, on le supprime
            $tweet->getLikes()->removeElement($existingLike);
            $entityManager->remove($existingLike);
            $entityManager->flush();

            return $this->json(['message' => 'Like retiré avec succès', 'likes' => count($tweet->getLikes())]);
        } else {
            // Sinon, on ajoute un nouveau like
            $like = new Likes();
            $like->setTweet($tweet);
            $like->setUser($user);
            $like->setDate(new \DateTime());

            $entityManager->persist($like);
            $entityManager->flush();

            return $this->json(['message' => 'Tweet liké avec succès', 'likes' => count($tweet->getLikes())]);
        }
    }
}
