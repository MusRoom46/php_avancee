<?php

namespace App\Controller;

use App\Entity\Likes;
use App\Entity\Tweets;
use App\Repository\LikesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class LikesController extends AbstractController
{
    #[Route('/api/tweets/{id}/like', name: 'api_tweet_toggle_like', methods: ['POST'])]
    public function toggleLike(
        Tweets $tweet,
        LikesRepository $likesRepository,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        Request $request
    ): JsonResponse {
        // Récupérer l'utilisateur depuis le corps de la requête
        $data = json_decode($request->getContent(), true);
        $userId = $data['user_id'] ?? null;

        if (!$userId) {
            return $this->json(['error' => 'User ID is required'], 400);
        }

        $user = $usersRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        // Vérifier si l'utilisateur a déjà liké ce tweet
        $existingLike = $likesRepository->findOneBy([
            'user' => $user,
            'tweet' => $tweet,
        ]);

        if ($existingLike) {
            // Si un like existe, le supprimer
            $entityManager->remove($existingLike);
            $entityManager->flush();

            return $this->json(['message' => 'Like removed']);
        } else {
            // Sinon, ajouter un nouveau like
            $like = new Likes();
            $like->setUser($user);
            $like->setTweet($tweet);

            $entityManager->persist($like);
            $entityManager->flush();

            return $this->json(['message' => 'Like added']);
        }
    }
}