<?php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Symfony\Component\Routing\Annotation\Route;

class AdvertController extends Controller
{
    //public function indexAction(Environment $twig)
    public function indexAction()
    {
        //return $this->render('OCPlatformBundle:Default:index.html.twig');
        //code auto-généré KO
        return $this->render('@OCPlatform/Advert/index.html.twig', ['name' => 'INDEX']);

        //$content = $twig->render('@OCPlatform/Advert/index.html.twig');
        //$content = Environment::$twig->render('@OCPlatform/Advert/index.html.twig', ['name' => 'Tofito']);
        //return new Response($content);
    }

    /**
     * @Route("/advert", name="oc_advert_index")
     */
    public function index(Environment $twig)
    {
        $content = $twig->render('Advert/index.html.twig', ['name' => 'winzou']);

        return new Response($content);
    }


    public function viewAction()
    {
        return $this->render('@OCPlatform/Advert/index.html.twig', ['name' => 'VIEW']);
    }

    public function addAction()
    {
        return $this->render('@OCPlatform/Advert/index.html.twig', ['name' => 'ADD']);
    }

}
