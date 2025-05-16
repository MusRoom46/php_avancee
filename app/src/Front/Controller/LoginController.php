<?php

namespace App\Front\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LoginController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private RequestStack $requestStack;

    public function __construct(HttpClientInterface $httpClient, RequestStack $requestStack)
    {
        $this->httpClient = $httpClient;
        $this->requestStack = $requestStack;
    }

    #[Route('/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $formData = $request->request->all();

            // Préparer les données pour l'API
            $data = [
                'email' => $formData['email'] ?? '',
                'password' => $formData['password'] ?? '',
            ];

            // Appeler l'API pour se connecter
            $response = $this->httpClient->request('POST', 'http://localhost/api/login', [
                'json' => $data,
            ]);

            // Vérifier la réponse de l'API
            if ($response->getStatusCode() === 200) {
                $responseData = $response->toArray();
                $token = $responseData['token'] ?? null;
                $user_id = $responseData['user']['id'] ?? null;
                $user_pseudo = $responseData['user']['pseudo'] ?? null;
                
                if ($token) {
                    // Stocker les infos du user dans la session
                    $session = $this->requestStack->getSession();
                    $session->set('jwt_token', $token);
                    $session->set('jwt_user_id', $user_id);
                    $session->set('jwt_user_pseudo', $user_pseudo);

                    $this->addFlash('success', 'Connexion réussie !');
                    return $this->redirectToRoute('timeline'); // Rediriger vers la page principale
                }
            }

            $this->addFlash('error', 'Erreur lors de la connexion : ' . $response->getContent(false));
        }

        // Afficher le formulaire de connexion
        return $this->render('login.html.twig');
    }

    #[Route('/logout', name: 'logout', methods: ['GET'])]
    public function logout(): Response
    {
        // Supprimer le token de la session
        $session = $this->requestStack->getSession();
        $session->remove('jwt_token');

        $this->addFlash('success', 'Déconnexion réussie !');
        return $this->redirectToRoute('login'); // Rediriger vers la page de connexion
    }
}