<?php

/*
   Alexandre Couedelo @ 2015-02-17 20:15:24
    Importé depuis Emagine/incipio
 */

namespace mgate\PersonneBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use mgate\PersonneBundle\Entity\Poste;

class LoadPosteData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $postes = array(
            //Bureau
            "Président",
            "Vice-président",
            "Trésorier",
            "Suiveur Manager Qualité",
            "Secrétaire général",
            //ca
            "Manager Qualité-Tréso",
            "Vice-Trésorier",
            "Binome Qualité",
            "Respo. Communication",
            "Respo. SI",
            "Respo. Dev'Co",
            //Membre
            "membre",
            "Intervenant",
            "Chef de Projet",
        );

        foreach($postes as $poste){
            $p = new Poste();
            $p->setIntitule($poste);

            $manager->persist($p);
        }
        $manager->flush();
    }
}

