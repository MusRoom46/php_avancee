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
        $userId = $session->get('user_id');
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

        return $this->render('profile.html.twig', [
            'user' => $user,
        ]);
    }
}