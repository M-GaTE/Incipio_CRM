<?php
/**
 * Created by PhpStorm.
 * User: Antoine
 * Date: 29/01/2017
 * Time: 10:36
 */

namespace Mgate\DashboardBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mgate\DashboardBundle\Entity\AdminParam;

/**
 * Loads the parameters modifiable through the admin form.
 *
 * Class LoadAdminFormData
 */
class LoadAdminParamData implements FixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $parameters = array(
        array('name' => 'nom', 'paramType' => 'string', 'defaultValue'=> 'N7 Consulting', 'required' => true, 'paramLabel' => 'Nom de la junior'),
        array('name' => 'nom', 'paramType' => 'string', 'defaultValue'=> 'N7C', 'required' => true, 'paramLabel' => 'Abbréviation de la junior'),
        array('name' => 'logo', 'paramType' => 'url', 'defaultValue'=> 'http://N7consulting.fr/themes/IOTH-bootstrap-basic/assets/images/logo_N7consulting.png', 'required' => true, 'paramLabel' => 'URL du logo de la Junior Entreprise'),
        array('name' => 'adresse', 'paramType' => 'string', 'defaultValue'=> '2 Rue Charles Camichel 31000 Toulouse', 'required' => true, 'paramLabel' => 'Adresse postale', ),
        array('name' => 'url', 'paramType' => 'url', 'defaultValue'=> 'http://n7consulting.fr', 'required' => true, 'paramLabel' => 'URL du site web'),
        array('name' => 'email', 'paramType' => 'string', 'defaultValue'=> 'contact@n7consulting.fr', 'required' => true, 'paramLabel' => 'Email de contact de la junior'),
        array('name' => 'domaineEmailEtu', 'paramType' => 'string', 'defaultValue'=> 'etu.enseeiht.fr', 'required' => true, 'paramLabel' => 'Suffixe des emails étudiants'),
        array('name' => 'domaineEmailAncien', 'paramType' => 'string', 'defaultValue'=> 'alumni.enseeiht.fr', 'required' => true, 'paramLabel' => 'Suffixe des emails alumni'),
        array('name' => 'presidentPrenom', 'paramType' => 'string', 'defaultValue'=> 'Antoine', 'required' => true, 'paramLabel' => 'Prenom du president'),
        array('name' => 'presidentNom', 'paramType' => 'string', 'defaultValue'=> 'Dupond', 'required' => true, 'paramLabel' => 'Nom du président'),
        array('name' => 'presidentTexte', 'paramType' => 'string', 'defaultValue'=> 'Son président,', 'required' => true, 'paramLabel' => 'Texte d\'annonce pour la signature président'),
        array('name' => 'tresorierPrenom', 'paramType' => 'string', 'defaultValue'=> 'Thomas', 'required' => true, 'paramLabel' => 'Prenom du trésorier'),
        array('name' => 'tresorierNom', 'paramType' => 'string', 'defaultValue'=> 'Dupont', 'required' => true, 'paramLabel' => 'Nom du trésorier'),
        array('name' => 'tresorierTexte', 'paramType' => 'string', 'defaultValue'=> 'Son trésorier,', 'required' => true, 'paramLabel' => 'Texte d\'annonce pour la signature trésorier'),
        array('name' => 'tva', 'paramType' => 'number', 'defaultValue'=> 0.2, 'required' => true, 'paramLabel' => 'Taux de TVA (20% -> 0.2)'),
        array('name' => 'anneCreation', 'paramType' => 'string', 'defaultValue'=> 'alumni.enseeiht.fr', 'required' => true, 'paramLabel' => 'Année de création de la junior'),
        array('name' => 'annee1Jeyser', 'paramType' => 'string', 'defaultValue'=> 'alumni.enseeiht.fr', 'required' => true, 'paramLabel' => 'Année de début d\'utilisation de Jeyser'),
        array('name' => 'gaTracking', 'paramType' => 'string', 'defaultValue'=> '', 'required' => false, 'paramLabel' => 'Code de suivi Google Analytics'),
        );

        $i =0;
        foreach ($parameters as $param){
            $p = new AdminParam();
            $p->setName($param['name']);
            $p->setParamType($param['paramType']);
            $p->setDefaultValue($param['defaultValue']);
            $p->setRequired($param['required']);
            $p->setParamLabel($param['paramLabel']);
            $p->setPriority(1000-$i*10);
            $manager->persist($p);
            $i++;
        }

        $manager->flush();
    }
}
