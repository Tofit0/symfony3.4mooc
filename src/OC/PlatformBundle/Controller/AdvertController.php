<?php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Symfony\Component\HttpFoundation\Response;
//use Twig\Environment;

class AdvertController extends Controller
{
    //public function indexAction(Environment $twig)
    public function indexAction()
    {
        return $this->render('@OCPlatform/Advert/index.html.twig', ['name' => 'ADVERT']);
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
