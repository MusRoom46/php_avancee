<?php

namespace App\Front\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RegisterController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $formData = $request->request->all();
            // Préparer les données pour l'API
            $data = [
                'pseudo' => $formData['pseudo'] ?? '',
                'email' => $formData['email'] ?? '',
                'mdp' => $formData['mdp'] ?? '',
            ];
            
            // Appeler l'API pour créer un compte
            $response = $this->httpClient->request('POST', 'http://localhost/api/register', [
                'json' => $data,
            ]);

            // Vérifier la réponse de l'API
            if ($response->getStatusCode() === 201) {
                $this->addFlash('success', 'Compte créé avec succès !');
                return $this->redirectToRoute('login'); // Rediriger vers la page de connexion
            }

            $this->addFlash('error', 'Erreur lors de la création du compte : ' . $response->getContent(false));
        }

        // Afficher le formulaire d'inscription
        return $this->render('register.html.twig');
    }
}