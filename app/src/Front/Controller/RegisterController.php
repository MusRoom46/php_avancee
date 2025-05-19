<?php

namespace App\Front\Controller;

use App\API\Entity\Users;
use App\Front\Form\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
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
        $user = new Users();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Préparer les données pour l'API
            $data = [
                'pseudo' => $user->getPseudo(),
                'email' => $user->getEmail(),
                'mdp' => $user->getPassword(),
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

            // Si l'API renvoie une erreur, on l'ajoute au formulaire
            $responseData = json_decode($response->getContent(false), true);
            if (isset($responseData['error'])) {
                $form->addError(new FormError($responseData['error']));
            } else {
                $this->addFlash('error', 'Erreur lors de la création du compte : ' . $response->getContent(false));
            }
        }

        // Afficher le formulaire d'inscription
        return $this->render('register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
