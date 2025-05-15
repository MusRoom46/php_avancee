<?php

namespace App\Controller;

use App\Entity\Likes;
use App\Entity\Tweet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

final class LikesController extends AbstractController
{
    #[Route('/api/tweets/{id}/likes', name: 'api_tweet_likes', methods: ['GET'])]
    #[OA\Get(
        path: "/api/tweets/{id}/likes",
        description: "Retourne la liste des likes pour un tweet spécifique",
        summary: "Récupère les likes d'un tweet",
        tags: ["Likes"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Identifiant unique du tweet",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des likes du tweet",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(
                                property: "user",
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "pseudo", type: "string"),
                                    new OA\Property(property: "email", type: "string"),
                                    new OA\Property(property: "avatar", type: "string"),
                                    new OA\Property(property: "date_creation", type: "string", format: "date-time")
                                ],
                                type: "object"
                            ),
                            new OA\Property(
                                property: "tweet",
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "content", type: "string"),
                                    new OA\Property(property: "date", type: "string", format: "date-time")
                                ],
                                type: "object"
                            ),
                            new OA\Property(property: "date", type: "string", format: "date-time")
                        ],
                        type: "object"
                    )
                )
            ),
            new OA\Response(
                response: 404,
                description: "Tweet non trouvé"
            )
        ]
    )]
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
    #[OA\Post(
        path: "/api/tweets/{id}/like",
        description: "Permet d'ajouter ou de retirer un like sur un tweet spécifique",
        summary: "Ajoute ou retire un like sur un tweet",
        security: [
            ["bearerAuth" => []]
        ],
        tags: ["Likes"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Identifiant unique du tweet",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Like ajouté ou retiré avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Tweet liké avec succès"),
                        new OA\Property(property: "likes", description: "Nombre total de likes sur le tweet", type: "integer")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 401,
                description: "Utilisateur non authentifié"
            ),
            new OA\Response(
                response: 404,
                description: "Tweet non trouvé"
            )
        ]
    )]
    public function like(Tweet $tweet, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

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
            $tweet->removeLike($existingLike);
            $user->removeLike($existingLike);
            $entityManager->persist($tweet);
            $entityManager->persist($user);
            $entityManager->remove($existingLike);
            $entityManager->flush();

            return $this->json(['message' => 'Like retiré avec succès', 'likes' => count($tweet->getLikes())]);
        } else {
            // Sinon, on ajoute un nouveau like
            $like = new Likes();
            $like->setTweet($tweet);
            $like->setUser($user);
            $like->setDate(new \DateTime());
            $tweet->addLike($like);
            $user->addLike($like);
            $entityManager->persist($tweet);
            $entityManager->persist($user);

            $entityManager->persist($like);
            $entityManager->flush();

            return $this->json(['message' => 'Tweet liké avec succès', 'likes' => count($tweet->getLikes())]);
        }
    }
}
