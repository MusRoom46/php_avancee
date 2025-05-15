<?php

namespace App\API\Controller;

use App\API\Entity\Follow;
use App\API\Repository\FollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class FollowController extends AbstractController
{
    #[Route('/api/follows', name: 'api_follows_list', methods: ['GET'])]
    #[OA\Get(
        path: "/api/follows",
        description: "Retourne la liste des utilisateurs suivis par l'utilisateur connecté",
        summary: "Récupère la liste des follows de l'utilisateur",
        tags: ["Follow"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des follows",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "date", type: "string", format: "date-time"),
                            new OA\Property(property: "user", type: "object", properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "pseudo", type: "string"),
                                new OA\Property(property: "email", type: "string")
                            ]),
                            new OA\Property(property: "user_suivi", type: "object", properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "pseudo", type: "string"),
                                new OA\Property(property: "email", type: "string")
                            ])
                        ],
                        type: "object"
                    )
                )
            )
        ]
    )]
    public function list(FollowRepository $followRepository): JsonResponse
    {
        $follows = $followRepository->findBy([
            'user' => $this->getUser(),
        ]);
        $data = array_map(fn(Follow $follow) => [
            'id' => $follow->getId(),
            'date' => $follow->getDate()->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $follow->getUser()->getId(),
                'pseudo' => $follow->getUser()->getPseudo(),
                'email' => $follow->getUser()->getEmail(),
            ],
            'user_suivi' => [
                'id' => $follow->getUserSuivi()->getId(),
                'pseudo' => $follow->getUserSuivi()->getPseudo(),
                'email' => $follow->getUserSuivi()->getEmail(),
            ],
        ], $follows);

        return $this->json($data);
    }

    #[Route('/api/follows', name: 'api_follows_create', methods: ['POST'])]
    #[OA\Post(
        path: "/api/follows",
        description: "Permet à l'utilisateur connecté de suivre un autre utilisateur",
        summary: "Suivre un utilisateur",
        requestBody: new OA\RequestBody(
            description: "Données pour suivre un utilisateur",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "user_suivi_id", type: "integer", example: 2)
                ],
                type: "object"
            )
        ),
        tags: ["Follow"],
        responses: [
            new OA\Response(
                response: 201,
                description: "Follow créé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Follow créé avec succès")
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

        $user = $this->getUser();

        // Vérification des données
        if (!isset($data['user_suivi_id'])) {
            return $this->json(['error' => 'Les IDs des utilisateurs sont requis'], 400);
        }

        $userSuivi = $entityManager->getReference('App\API\Entity\Users', $data['user_suivi_id']);

        $follow = new Follow();
        $follow->setDate(new \DateTime());
        $follow->setUser($user);
        $follow->setUserSuivi($userSuivi);

        $userSuivi->addFollower($follow);
        $user->addFollow($follow);

        $entityManager->persist($follow);
        $entityManager->flush();

        return $this->json(['message' => 'Follow créé avec succès'], 201);
    }

    #[Route('/api/follows/{id}', name: 'api_follows_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/follows/{id}",
        description: "Permet à l'utilisateur connecté d'arrêter de suivre un utilisateur",
        summary: "Arrêter de suivre un utilisateur",
        tags: ["Follow"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Identifiant unique du follow à supprimer",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Follow supprimé avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Follow supprimé avec succès")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Follow non trouvé"
            )
        ]
    )]
    public function delete(Follow $follow, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        $userSuivi = $follow->getUserSuivi();

        $userSuivi->removeFollower($follow);
        $user->removeFollow($follow);

        $entityManager->remove($follow);
        $entityManager->flush();

        return $this->json(['message' => 'Follow supprimé avec succès']);
    }
}
