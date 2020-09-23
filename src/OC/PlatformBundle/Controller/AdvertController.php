<?php

namespace OC\PlatformBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request; // Nouveau use
//use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Response;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;
use OC\PlatformBundle\Form\imageType;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
//use Twig\Environment;

class AdvertController extends Controller
{
    //public function indexAction(Environment $twig)
    public function indexAction($page)
    {
        if ($page < 1) {
            return  $this->is404();
        }

        $nbPerPage = 2;

        //$em = $this->getDoctrine()->getManager();

        // On récupère les annonces
        //$listAdverts = $em->getRepository('OCPlatformBundle:Advert')->findAll();
        $listAdverts = $this->getDoctrine()
            ->getManager()
            ->getRepository('OCPlatformBundle:Advert')
            ->getAdverts($page, $nbPerPage)
        ;

        // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
        $nbPages = ceil(count($listAdverts) / $nbPerPage);

        // Si la page n'existe pas, on retourne une 404
        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        // On donne toutes les informations nécessaires à la vue
        return $this->render('@OCPlatform/Advert/index.html.twig', array(
            'listAdverts' => $listAdverts,
            'nbPages'     => $nbPages,
            'page'        => $page,
        ));
    }

    /*
     * @Route("/advert/view/{id}", name="oc_advert_view")
     * http://127.0.0.1:8001/app_dev.php/advert/view/2?tag=developer
     */
    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
        // ou null si l'id $id  n'existe pas, d'où ce if :
        if (null === $advert) {
            return  $this->is404();
        }


        // On récupère la liste des candidatures de cette annonce
        $listApplications = $em
            ->getRepository('OCPlatformBundle:Application')
            ->findBy(array('advert' => $advert))
        ;


        // On récupère maintenant la liste des AdvertSkill
        $listAdvertSkills = $em
            ->getRepository('OCPlatformBundle:AdvertSkill')
            ->findBy(array('advert' => $advert))
        ;



        return $this->render('@OCPlatform/Advert/view.html.twig', array(
            'advert'           => $advert,
            'listApplications' => $listApplications,
            'listAdvertSkills' => $listAdvertSkills
        ));
    }


    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {
        // On crée un objet Advert
        $advert = new Advert();


        $form = $this->get('form.factory')->create(AdvertType::class, $advert);
       // $form = $this->createForm(AdvertType::class, $advert);

        // Si la requête est en POST
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid())
        {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $advert contient les valeurs entrées dans le formulaire par le visiteur


            // On vérifie que les valeurs entrées sont correctes
            // (Nous verrons la validation des objets en détail dans le prochain chapitre)

            // On enregistre notre objet $advert dans la base de données, par exemple
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            // On redirige vers la page de visualisation de l'annonce nouvellement créée
            return $this->redirectToRoute('oc_advert_view', array('id' => $advert->getId()));

        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
        return $this->render('@OCPlatform/Advert/add.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    public function delAction(Request $request, $id)
    {

        // Ici, on récupérera l'annonce correspondant à $id
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            return  $this->is404();
        }


        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

            return $this->redirectToRoute('oc_core_homepage');
        }

        return $this->render('@OCPlatform/Advert/delete.html.twig', array(
            'advert' => $advert,
            'form'   => $form->createView(),
        ));





//        $advertSkillRepository = $em->getRepository('OCPlatformBundle:AdvertSkill');
//
//
//        // On récupère les AdvertSkill liées à cette annonce
//        $advertSkills = $advertSkillRepository->findBy(array('advert' => $advert));
//
//        // Pour les supprimer toutes avant de pouvoir supprimer l'annonce elle-même
//        foreach ($advertSkills as $advertSkill) {
//            $em->remove($advertSkill);
//        }
//
//        // On peut maintenant supprimer l'annonce
//        $em->remove($advert);
//
//        $em->flush();
//
//        // Ici, on gérera la suppression de l'annonce en question
//        $this->addFlash('info', 'Merci votre annonce a bien été supprimée');
//
//        // Puis on redirige vers la page de visualisation de cette annonce
//        return $this->redirectToRoute('oc_core_homepage');
//        //return $this->render('@OCPlatform/Advert/delete.html.twig');

    }

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            $msg = "L'annonce d'id ".$id." n'existe pas.";
            return  $this->is404($msg);
        }

        //$form = $this->get('form.factory')->create(AdvertType::class, $advert);
        //On set l'image pour récupération
        //$this->createForm(imageType::class, $advert->getImage());
        $form = $this->createForm(AdvertEditType::class, $advert);


        // Si la requête est en POST
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid())
        {
            // On enregistre notre objet $advert dans la base de données, par exemple
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

            // On redirige vers la page de visualisation de l'annonce nouvellement créée
            return $this->redirectToRoute('oc_advert_view', array('id' => $advert->getId()));

        }


        return $this->render('@OCPlatform/Advert/edit.html.twig', array(
            'form' => $form->createView(),
            'advert' => $advert,
        ));
    }


    private function is404($msg = null)
    {
        // On crée la réponse sans lui donner de contenu pour le moment
        $response = new Response();

        // On définit le contenu
        $response->setContent("Ceci est une page d'erreur 404");

        // On définit le code HTTP à « Not Found » (erreur 404)
        $response->setStatusCode(Response::HTTP_NOT_FOUND);

        // On retourne la réponse
        return $this->render('@OCPlatform/Advert/404.html.twig', array('msg' => $msg));
    }

    public function menuAction($limit)
    {
        //$repoApp = $this->getDoctrine()->getManager()->getRepository('OCPlatformBundle:Application');
        //$listApplications = $repoApp->getApplicationsWithAdvert($limit);
        $em = $this->getDoctrine()->getManager();
        $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->findBy(
            array(),                 // Pas de critère
            array('date' => 'desc'), // On trie par date décroissante
            $limit,                  // On sélectionne $limit annonces
            0                        // À partir du premier
        );


        return $this->render('@OCPlatform/Advert/menu.html.twig', array(
            // Tout l'intérêt est ici : le contrôleur passe
            // les variables nécessaires au template !
            'listAdverts' => $listAdverts
        ));
    }

    private function getAdvertImagebyId ($id)
    {
        if($id != null)
        {
            $repo   = $this->getDoctrine()->getManager()->getRepository('OCPlatformBundle:Advert');
            $advert = $repo->find($id);
            if ($advert !== null)
            {
                //on recupère l'image
                $advert->getImage();

                return $advert;
            } else {

                return new Advert();
            }
        } else {
            return "merci de nous donner un ID valable :D";
        }

    }


    public function purgeAction($days = null, Request $request)
    {
        $servicePurge = $this->get('oc_platform.purger.advert');

        $servicePurge->purge($days);

        // On ajoute un message flash arbitraire
        $request->getSession()->getFlashBag()->add('info', 'Les annonces plus vieilles que '.$days.' jours ont été purgées.');

        // On redirige vers la page d'accueil
        return $this->redirectToRoute('oc_advert_index');

    }

    /**
     * @return Response
     * Test de validator
     */
    public function validatorAction()
    {
        $advert = new Advert;

        $advert->setDate(new \Datetime());  // Champ « date » OK
        $advert->setTitle('abc');           // Champ « title » incorrect : moins de 10 caractères
        //$advert->setContent('blabla');    // Champ « content » incorrect : on ne le définit pas
        $advert->setAuthor('A');            // Champ « author » incorrect : moins de 2 caractères

        // On récupère le service validator
        $validator = $this->get('validator');

        // On déclenche la validation sur notre object
        $listErrors = $validator->validate($advert);

        // Si $listErrors n'est pas vide, on affiche les erreurs
        if(count($listErrors) > 0) {
            // $listErrors est un objet, sa méthode __toString permet de lister joliement les erreurs
            return new Response((string) $listErrors);
        } else {
            return new Response("L'annonce est valide !");
        }
    }



}
