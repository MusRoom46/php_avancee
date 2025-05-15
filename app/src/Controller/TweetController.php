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
use OpenApi\Attributes as OA;

final class TweetController extends AbstractController
{
    #[Route('/api/tweets', name: 'api_tweets_list', methods: ['GET'])]
    #[OA\Get(
        path: "/api/tweets",
        description: "Retourne un tableau des tweets avec leurs informations détaillées",
        summary: "Récupère la liste des tweets",
        tags: ["Tweets"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des tweets",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "content", type: "string"),
                            new OA\Property(
                                property: "author",
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "pseudo", type: "string"),
                                    new OA\Property(property: "email", type: "string"),
                                    new OA\Property(property: "avatar", type: "string"),
                                    new OA\Property(property: "date_creation", type: "string", format: "date-time")
                                ],
                                type: "object"
                            ),
                            new OA\Property(property: "date", type: "string", format: "date-time"),
                            new OA\Property(property: "likes", type: "integer"),
                            new OA\Property(
                                property: "comments",
                                properties: [
                                    new OA\Property(property: "count", type: "integer"),
                                    new OA\Property(
                                        property: "comments",
                                        type: "array",
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: "id", type: "integer"),
                                                new OA\Property(property: "content", type: "string"),
                                                new OA\Property(
                                                    property: "author",
                                                    properties: [
                                                        new OA\Property(property: "id", type: "integer"),
                                                        new OA\Property(property: "pseudo", type: "string"),
                                                        new OA\Property(property: "email", type: "string"),
                                                        new OA\Property(property: "avatar", type: "string"),
                                                        new OA\Property(property: "date_creation", type: "string", format: "date-time")
                                                    ],
                                                    type: "object"
                                                ),
                                                new OA\Property(property: "date", type: "string", format: "date-time")
                                            ],
                                            type: "object"
                                        )
                                    )
                                ],
                                type: "object"
                            )
                        ],
                        type: "object"
                    )
                )
            )
        ]
    )]
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
    #[OA\Get(
        path: "/api/tweets/{id}",
        description: "Retourne les informations détaillées d'un tweet spécifique",
        summary: "Récupère les détails d'un tweet",
        tags: ["Tweets"],
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
                description: "Détails du tweet",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "content", type: "string"),
                        new OA\Property(
                            property: "author",
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "pseudo", type: "string"),
                                new OA\Property(property: "email", type: "string"),
                                new OA\Property(property: "avatar", type: "string"),
                                new OA\Property(property: "date_creation", type: "string", format: "date-time")
                            ],
                            type: "object"
                        ),
                        new OA\Property(property: "date", type: "string", format: "date-time"),
                        new OA\Property(
                            property: "likes",
                            properties: [
                                new OA\Property(property: "count", type: "integer"),
                                new OA\Property(
                                    property: "likes",
                                    type: "array",
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: "id", type: "integer"),
                                            new OA\Property(property: "content", type: "string"),
                                            new OA\Property(
                                                property: "author",
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "pseudo", type: "string"),
                                                    new OA\Property(property: "email", type: "string"),
                                                    new OA\Property(property: "avatar", type: "string"),
                                                    new OA\Property(property: "date_creation", type: "string", format: "date-time")
                                                ],
                                                type: "object"
                                            ),
                                            new OA\Property(property: "date", type: "string", format: "date-time")
                                        ],
                                        type: "object"
                                    )
                                )
                            ],
                            type: "object"
                        ),
                        new OA\Property(
                            property: "comments",
                            properties: [
                                new OA\Property(property: "count", type: "integer"),
                                new OA\Property(
                                    property: "comments",
                                    type: "array",
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: "id", type: "integer"),
                                            new OA\Property(property: "content", type: "string"),
                                            new OA\Property(
                                                property: "author",
                                                properties: [
                                                    new OA\Property(property: "id", type: "integer"),
                                                    new OA\Property(property: "pseudo", type: "string"),
                                                    new OA\Property(property: "email", type: "string"),
                                                    new OA\Property(property: "avatar", type: "string"),
                                                    new OA\Property(property: "date_creation", type: "string", format: "date-time")
                                                ],
                                                type: "object"
                                            ),
                                            new OA\Property(property: "date", type: "string", format: "date-time")
                                        ],
                                        type: "object"
                                    )
                                )
                            ],
                            type: "object"
                        )
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Tweet non trouvé"
            )
        ]
    )]
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
            'likes' => [
                'count' => count($tweet->getComments()),
                'likes' => array_map(fn($comment) => [
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
            ],
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
    #[OA\Post(
        path: "/api/tweets",
        description: "Permet de créer un nouveau tweet",
        summary: "Crée un nouveau tweet",
        requestBody: new OA\RequestBody(
            description: "Données du tweet à créer",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "content", description: "Contenu du tweet", type: "string")
                ],
                type: "object"
            )
        ),
        tags: ["Tweets"],
        responses: [
            new OA\Response(
                response: 201,
                description: "Tweet créé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Tweet créé avec succès")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 400,
                description: "Données invalides ou incomplètes",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Le contenu du tweet est requis")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 401,
                description: "Utilisateur non authentifié",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Utilisateur non authentifié")
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser(); // Récupère l'utilisateur actuellement connecté

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);

        // Vérifie si le contenu du tweet est fourni
        if (empty($data['content'])) {
            return $this->json(['error' => 'Le contenu du tweet est requis'], 400);
        }

        $tweet = new Tweet();
        $tweet->setContenu($data['content']);
        $tweet->setDate(new \DateTime());
        $tweet->setUser($user); // Associe l'utilisateur connecté au tweet

        $entityManager->persist($tweet);
        $entityManager->flush();

        return $this->json(['message' => 'Tweet créé avec succès'], 201);
    }

    #[Route('/api/tweets/{id}', name: 'api_tweets_update', methods: ['PUT'])]
    #[OA\Put(
        path: "/api/tweets/{id}",
        description: "Permet de mettre à jour un tweet existant",
        summary: "Met à jour un tweet",
        requestBody: new OA\RequestBody(
            description: "Données du tweet à mettre à jour",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "contenu", description: "Nouveau contenu du tweet", type: "string")
                ],
                type: "object"
            )
        ),
        tags: ["Tweets"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Identifiant unique du tweet à mettre à jour",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Tweet mis à jour avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Tweet mis à jour avec succès")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 400,
                description: "Données de mise à jour non valides",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Le \"contenu\" du tweet est requis")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Tweet non trouvé"
            )
        ]
    )]
    public function update(Request $request, Tweet $tweet, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérification des données
        if (!isset($data['contenu']) || empty($data['contenu'])) {
            return $this->json(['error' => 'Le "contenu" du tweet est requis'], 400);
        }

        $tweet->setContenu($data['contenu']);
        $entityManager->flush();

        return $this->json(['message' => 'Tweet mis à jour avec succès']);
    }

    #[Route('/api/tweets/{id}', name: 'api_tweets_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/tweets/{id}",
        description: "Supprime un tweet spécifique en fonction de son ID",
        summary: "Supprimer un tweet",
        tags: ["Tweets"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Identifiant unique du tweet à supprimer",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Suppression réussie du tweet",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Tweet supprimé avec succès")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Tweet non trouvé"
            )
        ]
    )]
    public function delete(Tweet $tweet, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($tweet);
        $entityManager->flush();

        return $this->json(['message' => 'Tweet supprimé avec succès']);
    }

}
