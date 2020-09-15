<?php

namespace OC\PlatformBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request; // Nouveau use
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
//use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Response;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
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

    /**
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



    public function addAction(Request $request)
    {
        // On récupère l'EntityManager
        $em = $this->getDoctrine()->getManager();

        $advert = new Advert();
        $advert->setTitle('Recherche dev Symfony, encore un');
        $advert->setAuthor('Francisco');
        $advert->setEmail('Francisco@maistascru.com');
        $advert->setContent('En remote pour plein de thune et une duree indeterminee');

        $image = new image();
        $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
        $image->setAlt('je vais y arriver!!!');

        // On récupère toutes les compétences possibles
        $listSkills = $em->getRepository('OCPlatformBundle:Skill')->findAll();

        // La méthode findAll retourne toutes les catégories de la base de données
        $listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();

        //on attache l'image à l'annonce
        $advert->setImage($image);

        // On boucle sur les catégories pour les lier à l'annonce
        foreach ($listCategories as $category) {
            $advert->addCategory($category);
        }

        // Création d'une première candidature
        $application1 = new Application();
        $application1->setAuthor('Marine');
        $application1->setContent("J'ai toutes les qualités requises.");
        $application1->setEmail('Marine@maistascru.com');

        // Création d'une deuxième candidature par exemple
        $application2 = new Application();
        $application2->setAuthor('Pierre');
        $application2->setContent("Je suis très motivé.");
        $application2->setEmail('Pierre@maistascru.com');

        // On lie les candidatures à l'annonce
        $application1->setAdvert($advert);
        $application2->setAdvert($advert);

        // Pour chaque compétence
        foreach ($listSkills as $skill) {
            // On crée une nouvelle « relation entre 1 annonce et 1 compétence »
            $advertSkill = new AdvertSkill();

            // On la lie à l'annonce, qui est ici toujours la même
            $advertSkill->setAdvert($advert);
            // On la lie à la compétence, qui change ici dans la boucle foreach
            $advertSkill->setSkill($skill);

            // Arbitrairement, on dit que chaque compétence est requise au niveau 'Expert'
            $advertSkill->setLevel('Expert');

            // Et bien sûr, on persiste cette entité de relation, propriétaire des deux autres relations
            $em->persist($advertSkill);
        }


        //on sauvegarde
        $em->persist($advert);

        // Étape 1 ter : pour cette relation pas de cascade lorsqu'on persiste Advert, car la relation est
        // définie dans l'entité Application et non Advert. On doit donc tout persister à la main ici.
        $em->persist($application1);
        $em->persist($application2);

        //on flush
        $em->flush();

        // La gestion d'un formulaire est particulière, mais l'idée est la suivante :

        // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
        if ($request->isMethod('POST')) {
            // Ici, on s'occupera de la création et de la gestion du formulaire
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            //$this->addFlash('notice', 'Annonce bien enregistrée.');

            // Puis on redirige vers la page de visualisation de cettte annonce
            return $this->redirectToRoute('oc_advert_view', array('id' => $advert->getId()));
        }


        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('@OCPlatform/Advert/add.html.twig', array('advert' => $advert));
    }

    public function delAction($id)
    {

        // Ici, on récupérera l'annonce correspondant à $id
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            return  $this->is404();
        }

        $advertSkillRepository = $em->getRepository('OCPlatformBundle:AdvertSkill');


        // On récupère les AdvertSkill liées à cette annonce
        $advertSkills = $advertSkillRepository->findBy(array('advert' => $advert));

        // Pour les supprimer toutes avant de pouvoir supprimer l'annonce elle-même
        foreach ($advertSkills as $advertSkill) {
            $em->remove($advertSkill);
        }

        // On peut maintenant supprimer l'annonce
        $em->remove($advert);

        $em->flush();

        // Ici, on gérera la suppression de l'annonce en question
        $this->addFlash('info', 'Merci votre annonce a bien été supprimée');

        // Puis on redirige vers la page de visualisation de cette annonce
        return $this->redirectToRoute('oc_core_homepage');
        //return $this->render('@OCPlatform/Advert/delete.html.twig');

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

        //si pas de catégories on les ajoutes
        if($advert->getCategories() === null) {
            // La méthode findAll retourne toutes les catégories de la base de données
            $listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();

            // On boucle sur les catégories pour les lier à l'annonce
            foreach ($listCategories as $category) {
                $advert->addCategory($category);
            }
        }



        $em->flush();

        // On modifie l'URL de l'image par exemple
        //$advert->getImage()->setUrl('data:image/jpeg;base64,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');
        //$advert->getImage()->setUrl('test.png');
        if ($request->isMethod('POST')) {
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

            return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
        }

        return $this->render('@OCPlatform/Advert/edit.html.twig', array(
            'advert' => $advert
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



}
