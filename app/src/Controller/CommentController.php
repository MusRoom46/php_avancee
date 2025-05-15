<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

final class CommentController extends AbstractController
{
    #[Route('/api/comments', name: 'api_comments_list', methods: ['GET'])]
    #[OA\Get(
        path: "/api/comments",
        description: "Retourne un tableau des commentaires avec leurs informations détaillées",
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
                            new OA\Property(
                                property: "tweet",
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "content", type: "string"),
                                    new OA\Property(property: "date", type: "string", format: "date-time"),
                                    new OA\Property(property: "likes", type: "integer")
                                ],
                                type: "object"
                            ),
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
                            )
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
        description: "Retourne les informations détaillées d'un commentaire spécifique",
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
                        new OA\Property(
                            property: "tweet",
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "content", type: "string"),
                                new OA\Property(property: "date", type: "string", format: "date-time"),
                                new OA\Property(property: "likes", type: "integer")
                            ],
                            type: "object"
                        ),
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
                        )
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

    #[Route('/api/comments', name: 'api_comments_create', methods: ['POST'])]
    #[OA\Post(
        path: "/api/comments",
        description: "Permet de créer un nouveau commentaire sur un tweet",
        summary: "Crée un nouveau commentaire",
        requestBody: new OA\RequestBody(
            description: "Données du commentaire à créer",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "contenu", description: "Contenu du commentaire", type: "string"),
                    new OA\Property(property: "tweet_id", description: "ID du tweet associé", type: "integer")
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
                description: "Données invalides ou incomplètes",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Le contenu du commentaire est requis")
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

    #[Route('/api/comments/{id}', name: 'api_comments_update', methods: ['PUT'])]
    #[OA\Put(
        path: "/api/comments/{id}",
        description: "Permet de mettre à jour un commentaire existant",
        summary: "Met à jour un commentaire",
        requestBody: new OA\RequestBody(
            description: "Données du commentaire à mettre à jour",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "contenu", description: "Nouveau contenu du commentaire", type: "string")
                ],
                type: "object"
            )
        ),
        tags: ["Commentaires"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Identifiant unique du commentaire à mettre à jour",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Commentaire mis à jour avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Commentaire mis à jour avec succès")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 400,
                description: "Données de mise à jour non valides",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Le \"contenu\" du commentaire est requis")
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
    public function update(Request $request, Comment $comment, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérification des données
        if (!isset($data['contenu']) || empty($data['contenu'])) {
            return $this->json(['error' => 'Le "contenu" du commentaire est requis'], 400);
        }

        $comment->setContenu($data['contenu']);
        $entityManager->flush();

        return $this->json(['message' => 'Commentaire mis à jour avec succès']);
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
                description: "Suppression réussie du commentaire",
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
