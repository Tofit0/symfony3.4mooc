<?php

namespace OC\PlatformBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request; // Nouveau use
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
//use Twig\Environment;

class AdvertController extends AbstractController
{
    //public function indexAction(Environment $twig)
    public function indexAction($page)
    {
        if ($page < 1) {
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }

        // Notre liste d'annonce en dur
        $listAdverts = array(
            array(
                'title'   => 'Recherche développpeur Symfony',
                'id'      => 1,
                'author'  => 'Alexandre',
                'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
                'date'    => new \Datetime()),
            array(
                'title'   => 'Mission de webmaster',
                'id'      => 2,
                'author'  => 'Hugo',
                'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
                'date'    => new \Datetime()),
            array(
                'title'   => 'Offre de stage webdesigner',
                'id'      => 3,
                'author'  => 'Mathieu',
                'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
                'date'    => new \Datetime())
        );

        return $this->render('@OCPlatform/Advert/index.html.twig', array(
            'listAdverts' => $listAdverts,
        ));
    }

    /**
     * @Route("/advert/view/{id}", name="oc_advert_view")
     * http://127.0.0.1:8001/app_dev.php/advert/view/2?tag=developer
     */
    public function viewAction($id)
    {

        $advert = array(
            'title'   => 'Recherche développpeur Symfony2',
            'id'      => $id,
            'author'  => 'Alexandre',
            'content' => 'Nous recherchons un développeur Symfony2 débutant sur Lyon. Blabla…',
            'date'    => new \Datetime()
        );

        return $this->render('@OCPlatform/Advert/view.html.twig', array(
            'advert' => $advert
        ));

        /*return $this->render('@OCPlatform/Advert/view.html.twig', [
            'id' => $id,
        ]);*/

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
        $this->addFlash('info', 'Merci votre annonce a bien été supprimée');

        // Puis on redirige vers la page de visualisation de cette annonce
        return $this->redirectToRoute('oc_core_homepage');
        //return $this->render('@OCPlatform/Advert/delete.html.twig');

    }

    public function editAction($id, Request $request)
    {
        // ...

        $advert = array(
            'title'   => 'Recherche développpeur Symfony',
            'id'      => $id,
            'author'  => 'Alexandre',
            'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
            'date'    => new \Datetime()
        );

        return $this->render('@OCPlatform/Advert/edit.html.twig', array(
            'advert' => $advert
        ));

        //return $this->render('@OCPlatform/Advert/edit.html.twig');
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

    public function menuAction($limit)
    {
        // On fixe en dur une liste ici, bien entendu par la suite
        // on la récupérera depuis la BDD !
        $listAdverts = array(
            array('id' => 2, 'title' => 'Recherche développeur Symfony'),
            array('id' => 5, 'title' => 'Mission de webmaster'),
            array('id' => 9, 'title' => 'Offre de stage webdesigner')
        );

        return $this->render('@OCPlatform/Advert/menu.html.twig', array(
            // Tout l'intérêt est ici : le contrôleur passe
            // les variables nécessaires au template !
            'listAdverts' => $listAdverts
        ));
    }

}
