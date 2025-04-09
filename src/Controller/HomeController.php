<?php

// src/Controller/HomeController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;  // Ajoute l'import de Response

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login'); // Redirection vers la page de login
        }

        // Si l'utilisateur est connecté, afficher la page d'accueil
        return $this->render('home/index.html.twig'); // Rendu du template
    }
}
