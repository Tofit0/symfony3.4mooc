<?php

namespace OC\PlatformBundle\Services\Purge;

use Doctrine\ORM\EntityManagerInterface;

class advertPurgeService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    // On injecte l'EntityManager
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function purge($days)
    {
        if ($days < 0) {
            throw $this->createNotFoundException("La variable day ".$days." n'est pas valide.");
        }

        $advertRepository      = $this->em->getRepository('OCPlatformBundle:Advert');
        $advertSkillRepository = $this->em->getRepository('OCPlatformBundle:AdvertSkill');

        // date d'il y a $days jours
        $date = new \Datetime($days.' days ago');

        // On récupère les annonces à supprimer
        $listAdverts = $advertRepository->getAdvertsBefore($date);

        // On parcourt les annonces pour les supprimer effectivement
        foreach ($listAdverts as $advert) {
            // On récupère les AdvertSkill liées à cette annonce
            $advertSkills = $advertSkillRepository->findBy(array('advert' => $advert));

            // Pour les supprimer toutes avant de pouvoir supprimer l'annonce elle-même
            foreach ($advertSkills as $advertSkill) {
                $this->em->remove($advertSkill);
            }

            // On peut maintenant supprimer l'annonce
            $this->em->remove($advert);
        }

        // Et on n'oublie pas de faire un flush !
        $this->em->flush();
    }
}