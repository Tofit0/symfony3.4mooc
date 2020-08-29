<?php

namespace OC\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        //return $this->render('OCCoreBundle:Default:index.html.twig');

        return $this->render('@OCCore/Default/index.html.twig');


    }

    public function contactAction()
    {
        // Le « flashBag » est ce qui contient les messages flash dans la session
        // Il peut bien sûr contenir plusieurs messages :
        $this->addFlash('info', 'Page de contact non disponible pour l\'instant...t\'as crus!!');

        // Puis on redirige vers la page de visualisation de cette annonce
        return $this->redirectToRoute('oc_core_homepage');


    }
}
