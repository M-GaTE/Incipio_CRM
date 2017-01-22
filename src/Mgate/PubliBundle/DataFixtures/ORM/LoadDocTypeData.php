<?php

namespace Mgate\PubliBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mgate\PubliBundle\Entity\Document;
use Mgate\StatBundle\Entity\Indicateur;

/**
 * Class LoadDocTypeData
 * @package Mgate\PubliBundle\DataFixtures\ORM
 * Creates default doctypes.
 */
class LoadDocTypeData implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        //avant-projet
        $ap = new Document();
        $ap->setName('AP');
        $ap->setPath('AP.docx');
        $ap->setSize(98000);
        $manager->persist($ap);

        //convention client
        $cc = new Document();
        $cc->setName('CC');
        $cc->setPath('CC.docx');
        $cc->setSize(31000);
        $manager->persist($cc);

        //bulletin adhésion
        $ba = new Document();
        $ba->setName('BA');
        $ba->setPath('BA.docx');
        $ba->setSize(20000);
        $manager->persist($ba);

        //proces verbal de reception final
        $pvr = new Document();
        $pvr->setName('PVR');
        $pvr->setPath('PVR.docx');
        $pvr->setSize(17000);
        $manager->persist($pvr);
        
        //recapitulatif de mission
        $rm = new Document();
        $rm->setName('RM');
        $rm->setPath('RM.docx');
        $rm->setSize(26000);
        $manager->persist($rm);
        
        
        $manager->flush();
    }
}
