<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class UsersController extends AbstractController
{
    #[Route('/api/users', name: 'api_users_list', methods: ['GET'])]
    public function list(UsersRepository $usersRepository): JsonResponse
    {
        $users = $usersRepository->findAll();
        $data = array_map(fn(Users $user) => [
            'id' => $user->getId(),
            'pseudo' => $user->getPseudo(),
            'email' => $user->getEmail(),
            'avatar' => $user->getAvatar(),
            'date_creation' => $user->getDateCreation()->format('Y-m-d H:i:s'),
        ], $users);

        return $this->json($data);
    }

    #[Route('/api/users/{id}', name: 'api_users_show', methods: ['GET'])]
    public function show(Users $user): JsonResponse
    {
        $data = [
            'id' => $user->getId(),
            'pseudo' => $user->getPseudo(),
            'email' => $user->getEmail(),
            'avatar' => $user->getAvatar(),
            'date_creation' => $user->getDateCreation()->format('Y-m-d H:i:s'),
        ];

        return $this->json($data);
    }

    #[Route('/api/users', name: 'api_users_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new Users();
        $user->setPseudo($data['pseudo']);
        $user->setEmail($data['email']);
        $user->setMdp($data['mdp']);
        $user->setAvatar($data['avatar'] ?? null);
        $user->setDateCreation(new \DateTime());

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'Utilisateur créé avec succès'], 201);
    }

    #[Route('/api/users/{id}', name: 'api_users_update', methods: ['PUT'])]
    public function update(Request $request, Users $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user->setPseudo($data['pseudo'] ?? $user->getPseudo());
        $user->setEmail($data['email'] ?? $user->getEmail());
        $user->setMdp($data['mdp'] ?? $user->getMdp());
        $user->setAvatar($data['avatar'] ?? $user->getAvatar());

        $entityManager->flush();

        return $this->json(['message' => 'Utilisateur mis à jour avec succès']);
    }

    #[Route('/api/users/{id}', name: 'api_users_delete', methods: ['DELETE'])]
    public function delete(Users $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['message' => 'Utilisateur supprimé avec succès']);
    }
}
