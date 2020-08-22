<?php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request; // Nouveau use
//use Twig\Environment;

class AdvertController extends AbstractController
{
    //public function indexAction(Environment $twig)
    public function indexAction($page)
    {
        // On ne sait pas combien de pages il y a
        // Mais on sait qu'une page doit être supérieure ou égale à 1
        if ($page < 1) {
            // On déclenche une exception NotFoundHttpException, cela va afficher
            // une page d'erreur 404 (qu'on pourra personnaliser plus tard d'ailleurs)
            throw $this->createNotFoundException('Page "'.$page.'" inexistante.');
        }

        // Ici, on récupérera la liste des annonces, puis on la passera au template

        // Mais pour l'instant, on ne fait qu'appeler le template
        return $this->render('@OCPlatform/Advert/index.html.twig');
    }

    /**
     * @Route("/advert/view/{id}", name="oc_advert_view")
     * http://127.0.0.1:8001/app_dev.php/advert/view/2?tag=developer
     */
    public function viewAction($id)
    {

        // Ici, on récupérera l'annonce correspondante à l'id $id

        return $this->render('@OCPlatform/Advert/view.html.twig', [
            'id' => $id,
        ]);

    }


    public function addAction(Request $request)
    {
        // La gestion d'un formulaire est particulière, mais l'idée est la suivante :

        // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
        if ($request->isMethod('POST')) {
            // Ici, on s'occupera de la création et de la gestion du formulaire

            $this->addFlash('notice', 'Annonce bien enregistrée.');

            // Puis on redirige vers la page de visualisation de cettte annonce
            return $this->redirectToRoute('oc_advert_view', ['id' => 5]);
        }

        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('@OCPlatform/Advert/add.html.twig');
    }

    public function delAction($id)
    {

        // Ici, on récupérera l'annonce correspondant à $id

        // Ici, on gérera la suppression de l'annonce en question

        return $this->render('@OCPlatform/Advert/delete.html.twig');

    }

    public function editAction($id, Request $request)
    {
        // Ici, on récupérera l'annonce correspondante à $id

        // Même mécanisme que pour l'ajout
        if ($request->isMethod('POST')) {
            $this->addFlash('notice', 'Annonce bien modifiée.');

            return $this->redirectToRoute('oc_advert_view', ['id' => 5]);
        }

        return $this->render('@OCPlatform/Advert/edit.html.twig');
    }


    private function is404($request)
    {
        // On crée la réponse sans lui donner de contenu pour le moment
        $response = new Response();

        // On définit le contenu
        $response->setContent("Ceci est une page d'erreur 404");

        // On définit le code HTTP à « Not Found » (erreur 404)
        $response->setStatusCode(Response::HTTP_NOT_FOUND);

        // On retourne la réponse
        return $response;
    }


}
