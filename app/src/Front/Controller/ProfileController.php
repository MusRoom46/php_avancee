<?php

namespace App\Front\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProfileController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private RequestStack $requestStack;

    public function __construct(HttpClientInterface $httpClient, RequestStack $requestStack)
    {
        $this->httpClient = $httpClient;
        $this->requestStack = $requestStack;
    }

    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        // Récupérer le token depuis la session
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à votre profil.');
            return $this->redirectToRoute('login');
        }

        // Décoder le token pour récupérer l'id utilisateur (si tu stockes l'id dans le token)
        // Sinon, tu peux stocker l'id dans la session lors du login
        $userId = $session->get('jwt_user_id');

        if (!$userId) {
            // Si tu ne stockes pas l'id, il faut le décoder depuis le JWT ici
            // Ou faire une route API qui retourne le profil du "current user" à partir du token
            $this->addFlash('error', 'Impossible de récupérer votre profil.');
            return $this->redirectToRoute('timeline');
        }
        
        // Appel à l'API pour récupérer toutes les infos du user
        $response = $this->httpClient->request('GET', "http://localhost/api/users/{$userId}/all", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);
        if ($response->getStatusCode() !== 200) {
            $this->addFlash('error', 'Erreur lors de la récupération du profil.');
            return $this->redirectToRoute('timeline');
        }

        $user = $response->toArray();

        // Appeler l'API pour récupérer les tweets
        $response = $this->httpClient->request('GET', "http://localhost/api/tweets/users/{$userId}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token, // Ajouter le token dans l'en-tête Authorization
            ],
        ]);
        
        // Vérifier si la réponse est valide
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de la récupération des tweets depuis l\'API');
        }

        // Décoder les données JSON
        $tweets = $response->toArray();

        return $this->render('profile.html.twig', [
            'user' => $user,
            'tweets' => $tweets,
        ]);
    }

    #[Route('/profile/{id}', name: 'profile_by_id')]
    public function profileById(int $id): Response
    {
        // Récupérer le token depuis la session
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à votre profil.');
            return $this->redirectToRoute('login');
        }

        // Décoder le token pour récupérer l'id utilisateur (si tu stockes l'id dans le token)
        // Sinon, tu peux stocker l'id dans la session lors du login
        $userId = $session->get('jwt_user_id');

        if (!$userId) {
            // Si tu ne stockes pas l'id, il faut le décoder depuis le JWT ici
            // Ou faire une route API qui retourne le profil du "current user" à partir du token
            $this->addFlash('error', 'Impossible de récupérer votre profil.');
            return $this->redirectToRoute('timeline');
        }
        
        // Appel à l'API pour récupérer toutes les infos du user
        $response = $this->httpClient->request('GET', "http://localhost/api/users/{$id}/all", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);
        if ($response->getStatusCode() !== 200) {
            $this->addFlash('error', 'Erreur lors de la récupération du profil.');
            return $this->redirectToRoute('timeline');
        }

        $user = $response->toArray();

        // Appeler l'API pour récupérer les tweets
        $response = $this->httpClient->request('GET', "http://localhost/api/tweets/users/{$id}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token, // Ajouter le token dans l'en-tête Authorization
            ],
        ]);
        
        // Vérifier si la réponse est valide
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de la récupération des tweets depuis l\'API');
        }

        // Décoder les données JSON
        $tweets = $response->toArray();

        return $this->render('profile.html.twig', [
            'user' => $user,
            'tweets' => $tweets,
        ]);
    }


    #[Route('/profile/avatar', name: 'profile_update_avatar', methods: ['POST'])]
    public function updateAvatar(): Response
    {
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');
        $userId = $session->get('jwt_user_id');

        if (!$token || !$userId) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('login');
        }

        $request = $this->requestStack->getCurrentRequest();
        $avatar = $request->request->get('avatar');

        if (!$avatar) {
            $this->addFlash('error', 'Aucun avatar sélectionné.');
            return $this->redirectToRoute('profile');
        }

        $response = $this->httpClient->request('PUT', "http://localhost/api/users/{$userId}/avatar", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'avatar' => $avatar,
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            $this->addFlash('success', 'Avatar mis à jour !');
        } else {
            $this->addFlash('error', 'Erreur lors de la mise à jour de l\'avatar.');
        }

        return $this->redirectToRoute('profile');
    }

    #[Route('/profile-like/{id}', name: 'profile_like_tweet', methods: ['POST'])]
    public function profileLikeTweet(int $id): Response
    {
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');
        $userId = $session->get('jwt_user_id');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour liker un tweet.');
            return $this->redirectToRoute('login');
        }

        // Appel API pour liker
        $response = $this->httpClient->request('POST', "http://localhost/api/tweets/{$id}/like", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->addFlash('error', 'Erreur lors de l\'ajout du like.');
        }

        return $this->redirectToRoute('profile');
    }
}