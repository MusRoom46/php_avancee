<?php

namespace App\Front\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


class TweetController extends AbstractController
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

    #[Route('/tweet/{id}', name: 'tweet_show', methods: ['GET'])]
    public function showTweet(int $id): Response
    {
        // Récupérer le token depuis la session
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('login'); // Rediriger vers la page de connexion
        }

        // Appeler l'API pour récupérer les détails du tweet
        $response = $this->httpClient->request('GET', 'http://localhost/api/tweets/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token, // Ajouter le token dans l'en-tête Authorization
            ],
        ]);

        if ($redirect = $this->handleApiResponse($response)) {
            return $redirect;
        }

        // Vérifier si la réponse est valide
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Erreur lors de la récupération des détails du tweet depuis l\'API');
        }

        // Décoder les données JSON
        $tweet = $response->toArray();

        // Rendre le template avec les détails du tweet
        return $this->render('tweet.html.twig', [
            'tweet' => $tweet,
        ]);
    }

    #[Route('/view-tweet-like/{id}', name: 'view_tweet_like_tweet', methods: ['POST'])]
    public function viewTweetLikeTweet(int $id): Response
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

        if ($redirect = $this->handleApiResponse($response)) {
            return $redirect;
        }

        if ($response->getStatusCode() !== 200) {
            $this->addFlash('error', 'Erreur lors de l\'ajout du like.');
        }

        return $this->redirectToRoute('tweet_show', ['id' => $id]);
    }
    
    #[Route('/tweet/{id}/comment', name: 'add_comment', methods: ['POST'])]
    public function addComment(int $id, Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour commenter.');
            return $this->redirectToRoute('login');
        }

        $contenu = $request->request->get('contenu');
        if (!$contenu) {
            $this->addFlash('error', 'Le commentaire ne peut pas être vide.');
            return $this->redirectToRoute('tweet_show', ['id' => $id]);
        }

        // Appel à l'API pour ajouter le commentaire
        $response = $this->httpClient->request('POST', 'http://localhost/api/comments', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
            'json' => [
                'contenu' => $contenu,
                'tweet_id' => $id,
            ],
        ]);

        if ($redirect = $this->handleApiResponse($response)) {
            return $redirect;
        }

        if ($response->getStatusCode() === 201) {
            $this->addFlash('success', 'Commentaire ajouté avec succès.');
        } else {
            $this->addFlash('error', 'Erreur lors de l\'ajout du commentaire.');
        }

        return $this->redirectToRoute('tweet_show', ['id' => $id]);
    }

    #[Route('/tweet/add', name: 'tweet_add', methods: ['POST'])]
    public function addTweet(Request $request): Response
    {
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour publier un tweet.');
            return $this->redirectToRoute('login');
        }

        $contenu = $request->request->get('content');
        if (!$contenu) {
            $this->addFlash('error', 'Le tweet ne peut pas être vide.');
            return $this->redirectToRoute('timeline');
        }

        // Appel API pour ajouter le tweet
        $response = $this->httpClient->request('POST', 'http://localhost/api/tweets', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
            'json' => [
                'content' => $contenu,
            ],
        ]);

        if ($redirect = $this->handleApiResponse($response)) {
            return $redirect;
        }

        if ($response->getStatusCode() === 201) {
            $this->addFlash('success', 'Tweet publié avec succès.');
        } else {
            $this->addFlash('error', 'Erreur lors de la publication du tweet.');
        }

        return $this->redirectToRoute('profile'); // Rediriger vers la timeline après l'ajout
    }

    #[Route('/tweet/{id}/delete', name: 'tweet_delete', methods: ['POST'])]
    public function deleteTweet(int $id): Response
    {
        $session = $this->requestStack->getSession();
        $token = $session->get('jwt_token');

        if (!$token) {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer un tweet.');
            return $this->redirectToRoute('login');
        }

        // Appel API pour supprimer le tweet
        $response = $this->httpClient->request('DELETE', "http://localhost/api/tweets/{$id}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);

        if ($redirect = $this->handleApiResponse($response)) {
            return $redirect;
        }

        if ($response->getStatusCode() === 204) {
            $this->addFlash('success', 'Tweet supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Erreur lors de la suppression du tweet.');
        }

        return $this->redirectToRoute('profile'); // Rediriger vers la timeline après la suppression
    }

}