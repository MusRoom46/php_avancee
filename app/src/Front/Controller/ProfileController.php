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

    private function handleApiResponse($response)
    {
        if ($response->getStatusCode() === 401) {
            $this->addFlash('error', 'Votre session a expiré, veuillez vous reconnecter.');
            return $this->redirectToRoute('login');
        }
        return null;
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
        
        if ($redirect = $this->handleApiResponse($response)) {
            return $redirect;
        }

        if ($response->getStatusCode() !== 200) {
            $this->addFlash('error', 'Erreur lors de la récupération du profil.');
            return $this->redirectToRoute('timeline');
        }

        $user = $response->toArray();
        $follows = $user['follows'] ?? [];
        $followers = $user['followers'] ?? [];

        // Appeler l'API pour récupérer les tweets
        $response = $this->httpClient->request('GET', "http://localhost/api/tweets/users/{$userId}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token, // Ajouter le token dans l'en-tête Authorization
            ],
        ]);
        
        if ($redirect = $this->handleApiResponse($response)) {
            return $redirect;
        }
        
        // Vérifier si la réponse est valide
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de la récupération des tweets depuis l\'API');
        }

        // Décoder les données JSON
        $tweets = $response->toArray();

        $isFollowing = false;
        if ($token && $userId != $session->get('jwt_user_id')) {
            $response = $this->httpClient->request('GET', 'http://localhost/api/follows', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]);

            if ($redirect = $this->handleApiResponse($response)) {
                return $redirect;
            }

            $follows = $response->toArray();
            foreach ($follows as $follow) {
                if ($follow['user_suivi']['id'] == $userId) {
                    $isFollowing = true;
                    break;
                }
            }
        }

        // Passe-les à la vue :
        return $this->render('profile.html.twig', [
            'user' => $user,
            'tweets' => $tweets,
            'follows' => $follows,
            'followers' => $followers,
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

        if ($redirect = $this->handleApiResponse($response)) {
            return $redirect;
        }

        if ($response->getStatusCode() === 200) {
            $this->addFlash('success', 'Avatar mis à jour !');
        } else {
            $this->addFlash('error', 'Erreur lors de la mise à jour de l\'avatar.');
        }

        return $this->redirectToRoute('profile');
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

        if ($redirect = $this->handleApiResponse($response)) {
            return $redirect;
        }

        if ($response->getStatusCode() !== 200) {
            $this->addFlash('error', 'Erreur lors de la récupération du profil.');
            return $this->redirectToRoute('timeline');
        }

        $user = $response->toArray();
        $follows = $user['follows'] ?? [];
        $followers = $user['followers'] ?? [];

        // Appeler l'API pour récupérer les tweets
        $response = $this->httpClient->request('GET', "http://localhost/api/tweets/users/{$id}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token, // Ajouter le token dans l'en-tête Authorization
            ],
        ]);
        
        if ($redirect = $this->handleApiResponse($response)) {
            return $redirect;
        }

        // Vérifier si la réponse est valide
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de la récupération des tweets depuis l\'API');
        }

        // Décoder les données JSON
        $tweets = $response->toArray();

        $isFollowing = false;
        if ($token && $id != $session->get('jwt_user_id')) {
            $response = $this->httpClient->request('GET', 'http://localhost/api/follows', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]);

            if ($redirect = $this->handleApiResponse($response)) {
                return $redirect;
            }

            $follows = $response->toArray();
            foreach ($follows as $follow) {
                if ($follow['user_suivi']['id'] == $id) {
                    $isFollowing = true;
                    break;
                }
            }
        }

        // Passe-les à la vue :
        return $this->render('profile.html.twig', [
            'user' => $user,
            'tweets' => $tweets,
            'isFollowing' => $isFollowing,
            'follows' => $follows,
            'followers' => $followers,
        ]);
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

        if ($redirect = $this->handleApiResponse($response)) {
            return $redirect;
        }

        if ($response->getStatusCode() !== 200) {
            $this->addFlash('error', 'Erreur lors de l\'ajout du like.');
        }

        return $this->redirectToRoute('profile');
    }

    #[Route('/profile/{id}/follow', name: 'follow_user', methods: ['POST'])]
    public function followUser(int $id): Response
    {
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('login');
        }

        $response = $this->httpClient->request('POST', 'http://localhost/api/follows', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
            'json' => [
                'user_suivi_id' => $id,
            ],
        ]);

        if ($redirect = $this->handleApiResponse($response)) {
            return $redirect;
        }

        if ($response->getStatusCode() === 201) {
            $this->addFlash('success', 'Vous suivez maintenant cet utilisateur.');
        } else {
            $this->addFlash('error', 'Erreur lors du suivi.');
        }

        return $this->redirectToRoute('profile_by_id', ['id' => $id]);
    }

    #[Route('/profile/{id}/unfollow', name: 'unfollow_user', methods: ['POST'])]
    public function unfollowUser(int $id): Response
    {
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('login');
        }

        // On récupère l'id du follow via l'API (ou tu peux le stocker côté front si tu l'as déjà)
        $response = $this->httpClient->request('GET', 'http://localhost/api/follows', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);
        $follows = $response->toArray();
        $followId = null;
        foreach ($follows as $follow) {
            if ($follow['user_suivi']['id'] == $id) {
                $followId = $follow['user_suivi']['id'];
                break;
            }
        }
        if (!$followId) {
            $this->addFlash('error', 'Relation de suivi non trouvée.');
            return $this->redirectToRoute('profile_by_id', ['id' => $id]);
        }

        $response = $this->httpClient->request('DELETE', "http://localhost/api/follows/{$followId}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            $this->addFlash('success', 'Vous ne suivez plus cet utilisateur.');
        } else {
            $this->addFlash('error', 'Erreur lors de l\'unfollow.');
        }

        return $this->redirectToRoute('profile_by_id', ['id' => $id]);
    }

}