<?php

namespace App\Front\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


class TimelineController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private RequestStack $requestStack;

    public function __construct(HttpClientInterface $httpClient, RequestStack $requestStack)
    {
        $this->httpClient = $httpClient;
        $this->requestStack = $requestStack;
    }

    #[Route('/', name: 'timeline')]
    public function timeline(): Response
    {
        // Récupérer le token depuis la session
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');
        
        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('login'); // Rediriger vers la page de connexion
        }

        // Appeler l'API pour récupérer les tweets
        $response = $this->httpClient->request('GET', 'http://localhost/api/tweets', [
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

        // Rendre le template avec la variable 'tweets'
        return $this->render('timeline.html.twig', [
            'tweets' => $tweets,
        ]);
    }

    #[Route('/timeline-like/{id}', name: 'timeline_like_tweet', methods: ['POST'])]
    public function TimelineLikeTweet(int $id): Response
    {
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');

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

        // Rediriger vers la timeline avec un fragment
        $url = $this->generateUrl('timeline') . '#tweet-' . $id;
        return new RedirectResponse($url);
    }

    #[Route('/search/user', name: 'search_user', methods: ['GET'])]
    public function search_user(Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');
        $query = $request->query->get('q');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour rechercher.');
            return $this->redirectToRoute('login');
        }

        $users = [];
        if ($query) {
            $response = $this->httpClient->request('GET', 'http://localhost/api/users/search/by-pseudo', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'pseudo' => $query,
                ],
            ]);
            if ($response->getStatusCode() === 200) {
                $users = $response->toArray();
            } else {
                $this->addFlash('error', 'Aucun utilisateur trouvé.');
            }
        }

        return $this->render('search_user.html.twig', [
            'users' => $users,
            'query' => $query,
        ]);
    }

    #[Route('/search/tweet', name: 'search_tweet', methods: ['GET'])]
    public function search_tweet(Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');
        $query = $request->query->get('q');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour rechercher.');
            return $this->redirectToRoute('login');
        }

        $tweets = [];
        if ($query) {
            $response = $this->httpClient->request('GET', 'http://localhost/api/tweets/search/by-content', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'q' => $query,
                ],
            ]);
            if ($response->getStatusCode() === 200) {
                $tweets = $response->toArray();
            } else {
                $this->addFlash('error', 'Aucun tweet trouvé.');
            }
        }

        return $this->render('search_tweet.html.twig', [
            'tweets' => $tweets,
            'query' => $query,
        ]);
    }
}