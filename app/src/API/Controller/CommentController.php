<?php

namespace App\API\Controller;

use App\API\Entity\Comment;
use App\API\Entity\Tweet;
use App\API\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class CommentController extends AbstractController
{
    #[Route('/api/comments', name: 'api_comments_list', methods: ['GET'])]
    #[OA\Get(
        path: "/api/comments",
        description: "Retourne un tableau des commentaires avec leurs informations",
        summary: "Récupère la liste des commentaires",
        tags: ["Commentaires"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des commentaires",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "date", type: "string", format: "date-time"),
                            new OA\Property(property: "contenu", type: "string"),
                            new OA\Property(property: "tweet", type: "object", properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "content", type: "string"),
                                new OA\Property(property: "date", type: "string", format: "date-time"),
                                new OA\Property(property: "likes", type: "integer")
                            ]),
                            new OA\Property(property: "user", type: "object", properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "pseudo", type: "string"),
                                new OA\Property(property: "email", type: "string"),
                                new OA\Property(property: "avatar", type: "string"),
                                new OA\Property(property: "date_creation", type: "string", format: "date-time")
                            ])
                        ],
                        type: "object"
                    )
                )
            )
        ]
    )]
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
    #[OA\Get(
        path: "/api/comments/{id}",
        description: "Retourne les informations d'un commentaire spécifique en fonction de l'ID",
        summary: "Récupère les détails d'un commentaire",
        tags: ["Commentaires"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Identifiant unique du commentaire",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Détails du commentaire",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "date", type: "string", format: "date-time"),
                        new OA\Property(property: "contenu", type: "string"),
                        new OA\Property(property: "tweet", type: "object", properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "content", type: "string"),
                            new OA\Property(property: "date", type: "string", format: "date-time"),
                            new OA\Property(property: "likes", type: "integer")
                        ]),
                        new OA\Property(property: "user", type: "object", properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "pseudo", type: "string"),
                            new OA\Property(property: "email", type: "string"),
                            new OA\Property(property: "avatar", type: "string"),
                            new OA\Property(property: "date_creation", type: "string", format: "date-time")
                        ])
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Commentaire non trouvé"
            )
        ]
    )]
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
    #[OA\Get(
        path: "/api/tweets/{id}/comments",
        description: "Retourne tous les commentaires d'un tweet spécifique",
        summary: "Récupère les commentaires d'un tweet",
        tags: ["Commentaires", "Tweets"],
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
                description: "Tweet avec ses commentaires",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "content", type: "string"),
                        new OA\Property(property: "date", type: "string", format: "date-time"),
                        new OA\Property(property: "comments", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "contenu", type: "string"),
                                new OA\Property(property: "date", type: "string", format: "date-time"),
                                new OA\Property(property: "user", properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "pseudo", type: "string"),
                                    new OA\Property(property: "email", type: "string"),
                                    new OA\Property(property: "avatar", type: "string"),
                                    new OA\Property(property: "date_creation", type: "string", format: "date-time")
                                ], type: "object")
                            ],
                            type: "object"
                        )),
                        new OA\Property(property: "user", properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "pseudo", type: "string"),
                            new OA\Property(property: "email", type: "string"),
                            new OA\Property(property: "avatar", type: "string"),
                            new OA\Property(property: "date_creation", type: "string", format: "date-time")
                        ], type: "object")
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
    #[OA\Post(
        path: "/api/comments",
        description: "Permet de créer un nouveau commentaire",
        summary: "Création d'un commentaire",
        requestBody: new OA\RequestBody(
            description: "Données pour la création d'un commentaire",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "contenu", type: "string", example: "Contenu du commentaire"),
                    new OA\Property(property: "tweet_id", type: "integer", example: 1)
                ],
                type: "object"
            )
        ),
        tags: ["Commentaires"],
        responses: [
            new OA\Response(
                response: 201,
                description: "Commentaire créé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Commentaire créé avec succès")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 400,
                description: "Données invalides"
            ),
            new OA\Response(
                response: 401,
                description: "Utilisateur non authentifié"
            )
        ]
    )]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $comment = new Comment();
        $comment->setDate(new \DateTime());
        $comment->setContenu($data['contenu']);
        $comment->setTweet($entityManager->getReference('App\API\Entity\Tweet', $data['tweet_id']));

        // Récupère l'utilisateur
        $user = $this->getUser();
        $comment->setUser($user);
        $user->addComment($comment);

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->json(['message' => 'Commentaire créé avec succès'], 201);
    }

    #[Route('/api/comments/{id}', name: 'api_comments_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/comments/{id}",
        description: "Supprime un commentaire spécifique en fonction de son ID",
        summary: "Supprimer un commentaire",
        tags: ["Commentaires"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Identifiant unique du commentaire à supprimer",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Commentaire supprimé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Commentaire supprimé avec succès")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Commentaire non trouvé"
            )
        ]
    )]
    public function delete(Comment $comment, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->json(['message' => 'Commentaire supprimé avec succès']);
    }
}
