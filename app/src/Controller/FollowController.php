<?php

namespace App\Controller;

use App\Entity\Follow;
use App\Repository\FollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class FollowController extends AbstractController
{
    #[Route('/api/follows', name: 'api_follows_list', methods: ['GET'])]
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
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->getUser();

        // Vérification des données
        if (!isset($data['user_suivi_id'])) {
            return $this->json(['error' => 'Les IDs des utilisateurs sont requis'], 400);
        }

        $userSuivi = $entityManager->getReference('App\Entity\Users', $data['user_suivi_id']);

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