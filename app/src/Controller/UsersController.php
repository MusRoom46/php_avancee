<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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

    #[Route('/api/users/{id}/all', name: 'api_users_show_all_info', methods: ['GET'])]
    public function showAllInfoUser(Users $user): JsonResponse
    {
        // Vérifier si l'utilisateur est correct
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }
        $data = [
            'id' => $user->getId(),
            'pseudo' => $user->getPseudo(),
            'email' => $user->getEmail(),
            'avatar' => $user->getAvatar(),
            'date_creation' => $user->getDateCreation()->format('Y-m-d H:i:s'),
            'tweets' => array_map(fn($tweet) => [
                'id' => $tweet->getId(),
                'content' => $tweet->getContenu(),
                'date' => $tweet->getDate()->format('Y-m-d H:i:s'),
            ], $user->getTweets()->toArray()),
            'likes' => array_map(fn($like) => [
                'id' => $like->getId(),
                'date' => $like->getDate()->format('Y-m-d H:i:s'),
                'tweet' => [
                    'id' => $like->getTweet()->getId(),
                    'content' => $like->getTweet()->getContenu(),
                    'date' => $like->getTweet()->getDate()->format('Y-m-d H:i:s'),
                ],
            ], $user->getLikes()->toArray()),
            'comments' => array_map(fn($comment) => [
                'id' => $comment->getId(),
                'content' => $comment->getContenu(),
                'date' => $comment->getDate()->format('Y-m-d H:i:s'),
                'tweet' => [
                    'id' => $comment->getTweet()->getId(),
                    'content' => $comment->getTweet()->getContenu(),
                    'date' => $comment->getTweet()->getDate()->format('Y-m-d H:i:s'),
                ],
            ], $user->getComments()->toArray()),
            'follows' => array_map(fn($follow) => [
                'id' => $follow->getId(),
                'date' => $follow->getDate()->format('Y-m-d H:i:s'),
                'user_suivi' => [
                    'id' => $follow->getUserSuivi()->getId(),
                    'pseudo' => $follow->getUserSuivi()->getPseudo(),
                    'email' => $follow->getUserSuivi()->getEmail(),
                ],
            ], $user->getFollows()->toArray()),
            'followers' => array_map(fn($follower) => [
                'id' => $follower->getId(),
                'date' => $follower->getDate()->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => $follower->getUser()->getId(),
                    'pseudo' => $follower->getUser()->getPseudo(),
                    'email' => $follower->getUser()->getEmail(),
                ],
            ], array_filter($user->getFollowers()->toArray(), fn($follow) => $follow->getUserSuivi() === $user)),
            'tweets_count' => count($user->getTweets()),
            'likes_count' => count($user->getLikes()),
            'comments_count' => count($user->getComments()),
            'follows_count' => count($user->getFollows()),
            'followers_count' => count($user->getFollowers())
        ];

        return $this->json($data);
    }

    #[Route('/api/users/{id}', name: 'api_users_show', methods: ['GET'])]
    public function show(Users $user): JsonResponse
    {
        // Vérifier si l'utilisateur est correct
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }
        $data = [
            'id' => $user->getId(),
            'pseudo' => $user->getPseudo(),
            'email' => $user->getEmail(),
            'avatar' => $user->getAvatar(),
            'date_creation' => $user->getDateCreation()->format('Y-m-d H:i:s'),
        ];

        return $this->json($data);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new Users();
        $user->setPseudo($data['pseudo']);
        $user->setEmail($data['email']);
        $user->setDateCreation(new \DateTime());

        // Hash du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $data['mdp']);
        $user->setMdp($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'Inscription réussie'], 201);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UsersRepository $usersRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $JWTManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // Recherche de l'utilisateur par email
        $user = $usersRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        // Vérification du mot de passe
        if (!$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['message' => 'Mot de passe incorrect'], 401);
        }

        // Générer un JWT
        $token = $JWTManager->create($user);

        return $this->json(['token' => $token], 200);
    }


    #[Route('/api/users/{id}', name: 'api_users_update', methods: ['PUT'])]
    public function update(Request $request, Users $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user->setPseudo($data['pseudo'] ?? $user->getPseudo());
        $user->setEmail($data['email'] ?? $user->getEmail());
        $user->setMdp($data['mdp'] ?? $user->getPassword());
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
