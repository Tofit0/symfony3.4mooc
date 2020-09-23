<?php
// src/OC/PlatformBundle/Twig/AntispamExtension.php

namespace OC\PlatformBundle\Twig;

use OC\PlatformBundle\Antispam\OCAntispam;

class AntispamExtension extends \Twig_Extension
{
    /**
     * @var OCAntispam
     */
    private $ocAntispam;

    public function __construct(OCAntispam $ocAntispam)
    {
        $this->ocAntispam = $ocAntispam;
    }


    // Twig va exécuter cette méthode pour savoir quelle(s) fonction(s) ajoute notre service
    // Dans ce cas on pourra exécuter la fonction checkIfSpam depuis les vues twigs
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('checkIfSpam', array($this, 'checkIfArgumentIsSpam')),
        );
    }


    public function checkIfArgumentIsSpam($text)
    {
        return $this->ocAntispam->isSpam($text);
    }
}