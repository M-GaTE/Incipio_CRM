<?php

namespace Mgate\DashboardBundle\Command;

use Mgate\PersonneBundle\Entity\Employe;
use Mgate\PersonneBundle\Entity\Filiere;
use Mgate\PersonneBundle\Entity\Membre;
use Mgate\PersonneBundle\Entity\Personne;
use Mgate\PersonneBundle\Entity\Prospect;
use Mgate\SuiviBundle\Entity\Ap;
use Mgate\SuiviBundle\Entity\Cc;
use Mgate\SuiviBundle\Entity\Etude;
use Mgate\SuiviBundle\Entity\GroupePhases;
use Mgate\SuiviBundle\Entity\Mission;
use Mgate\SuiviBundle\Entity\Phase;
use Mgate\SuiviBundle\Entity\ProcesVerbal;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDataCommand extends ContainerAwareCommand
{
    const NOM = array('Henry', 'Martinez', 'Durand', 'Duval', 'Leroux', 'Robert', 'Morel', 'Bourgeois', 'Dupont', 'Dumont', 'Bernard', 'Francois', 'Dupond', 'Dubois', 'Blanc', 'Paul', 'Petit');
    const PRENOM = array('Alexandre', 'Paul', 'Thomas', 'Raphaël', 'Camille', 'Inès', 'Emma', 'Gabriel', 'Antoine', 'Louis', 'Victor', 'Maxime', 'Hugo', 'Louise', 'Marie', 'Sarah', 'Arthur', 'Clara', 'Lea', 'Alice', 'Lucas', 'Jules', 'Chloe', 'Elsa', 'Manon');

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('demo:create_data')
            ->setDescription('Create some demonstration data')
            ->setHelp('Creates some fake data for every module in order to have a nice overview of all functionnality.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $array_projects = array(
            array(
                'nom' => '315GLA',
                'description' => 'Realisation site web',
                'statut' => 1,
                'nbrJEH' => 9,
                'duree' => 5,
                'dateCC' => 'ok',
                'prospect' => array('entreprise' => 'Gladiator Consulting',
                    'adresse' => '3 rue du chene noir',
                    'codePostal' => 33100,
                    'ville' => 'Toulouse',
                    'entite' => 2,
                    'email' => 'contact@glad.fr',
                ),
            ),
            array(
                'nom' => '316BLA',
                'description' => 'Electronique avancee',
                'statut' => 2,
                'nbrJEH' => 5,
                'duree' => 3,
                'dateCC' => 'ok',
                'prospect' => array('entreprise' => 'Blackwater',
                    'adresse' => '1020 5th Avenue',
                    'codePostal' => 92200,
                    'ville' => 'Neuilly',
                    'entite' => 3,
                    'email' => 'hello@black.ninja',
                ),
            ),
            array(
                'nom' => '317IMU',
                'description' => 'Design Base de donnes',
                'statut' => 4,
                'nbrJEH' => 8,
                'duree' => 4,
                'dateCC' => 'ok',
                'prospect' => array('entreprise' => 'Imuka',
                    'adresse' => 'Kuruma San',
                    'codePostal' => 91000,
                    'ville' => 'Evry',
                    'entite' => 4,
                    'email' => 'contact@imuka.jp',
                ),
            ),
            array(
                'nom' => '319UNI',
                'description' => 'Conception Radar recul',
                'statut' => 4,
                'nbrJEH' => 12,
                'duree' => 8,
                'dateCC' => 'ok',
                'prospect' => array('entreprise' => 'Universal rad',
                    'adresse' => '2 rue Marie Curie',
                    'codePostal' => 35000,
                    'ville' => 'Rennes',
                    'entite' => 5,
                    'email' => 'contact@univ.radar',
                ),
            ),
            array(
                'nom' => '320TEK',
                'description' => 'Refactorisation code Java',
                'statut' => 2,
                'nbrJEH' => 10,
                'duree' => 8,
                'dateCC' => 'ok',
                'prospect' => array('entreprise' => 'Teknik studio',
                    'adresse' => '10 impasse sunderland',
                    'codePostal' => 35000,
                    'ville' => 'Rennes',
                    'entite' => 6,
                    'email' => 'contact@teknik.paris',
                ),
            ),
            array(
                'nom' => '321DUV',
                'description' => 'Calcul de flux thermique',
                'statut' => 2,
                'nbrJEH' => 9,
                'duree' => 4,
                'dateCC' => 'ok',
                'prospect' => array('entreprise' => 'Duvilcolor',
                    'adresse' => '600 la pyrennene ',
                    'codePostal' => 33100,
                    'ville' => 'Labege',
                    'entite' => 4,
                    'email' => 'contact@duvilcol.or',
                ),
            ),
            array(
                'nom' => '322NIL',
                'description' => 'Application Android',
                'statut' => 1,
                'nbrJEH' => 8,
                'duree' => 12,
                'dateCC' => 'ok',
                'prospect' => array('entreprise' => 'Nilsen Industries',
                    'adresse' => '2 rue saint-louis',
                    'codePostal' => 31000,
                    'ville' => 'Bordeaux',
                    'entite' => 7,
                    'email' => 'contact@nislen.com',
                ),
            ),
            array(
                'nom' => '323PRR',
                'description' => 'Etude de faisabilite',
                'statut' => 3,
                'nbrJEH' => 4,
                'duree' => 4,
                'dateCC' => 'ok',
                'prospect' => array('entreprise' => 'PRR',
                    'adresse' => 'PRR',
                    'codePostal' => 35000,
                    'ville' => 'Rennes',
                    'entite' => 4,
                    'email' => 'contact@prr.cn',
                ),
            ),
        );

        /* Filiere management */
        $filieres = array('Info', 'Hydro', 'Electronique', 'Telecoms', 'Automatique');

        foreach ($filieres as $ff){
            $nf = new Filiere();
            $nf->setDescription('Demo filiere');
            $nf->setNom($ff);
            $em->persist($nf);
        }

        $inserted_projects = 0;
        $inserted_prospects = 0;

        foreach ($array_projects as $etude) {

            //create project
            $e = new Etude();
            ++$inserted_projects;
            $mandat = rand(2013, 2016);
            $month = rand(1, 10);
            $day = rand(1, 30);
            $e->setMandat($mandat);
            $e->setNom($etude['nom']);
            $e->setDescription($etude['description']);
            $e->setDateCreation(new \DateTime($mandat.'-'.$month.'-'.$day));
            $e->setStateID($etude['statut']);
            $e->setAcompte(true);
            $e->setPourcentageAcompte(0.3);
            $e->setFraisDossier(90);
            $e->setPresentationProjet('Presentation '.$etude['description']);
            $e->setDescriptionPrestation('Describe what we will do here');
            $e->setSourceDeProspection(rand(1, 10));
            $em->persist($e);
            $c = $em->getRepository('N7consultingRhBundle:Competence')->find(rand(1, 12));
            if ($c !== null) {
                $c->addEtude($e);
            }

            /* Prospect management */
            $p = new Prospect();
            ++$inserted_prospects;
            $p->setNom($etude['prospect']['entreprise']);
            $p->setAdresse($etude['prospect']['adresse']);
            $p->setCodePostal($etude['prospect']['codePostal']);
            $p->setVille($etude['prospect']['ville']);
            $p->setEntite($etude['prospect']['entite']);

            $pe = new Personne();
            $pe->setPrenom(self::PRENOM[array_rand(self::PRENOM)]); //whitespace explode : not perfect but better than nothing
            $pe->setNom(self::NOM[array_rand(self::NOM)]);
            $pe->setEmailEstValide(true);
            $pe->setEstAbonneNewsletter(false);
            $pe->setEmail($etude['prospect']['email']);
            $pe->setAdresse($etude['prospect']['adresse']);
            $pe->setCodePostal($etude['prospect']['codePostal']);
            $pe->setVille($etude['prospect']['ville']);

            $emp = new Employe();
            $emp->setProspect($p);
            $p->addEmploye($emp);
            $emp->setPersonne($pe);
            $em->persist($emp->getPersonne());
            $em->persist($emp);
            $em->persist($p);
            $e->setProspect($p);

            //create phases
            $g = new GroupePhases(); //default group
            $g->setTitre('Random generated'.rand());
            $g->setNumero(1);
            $g->setDescription('Automatic description');
            $g->setEtude($e);
            $em->persist($g);

            $ph = new Phase();
            $ph->setEtude($e);
            $ph->setGroupe($g);
            $ph->setPosition(0);
            $ph->setNbrJEH($etude['nbrJEH']);
            $ph->setPrixJEH(340);
            $ph->setTitre('Default phase');
            $ph->setDelai($etude['duree'] * 7);
            $ph->setDateDebut(new \DateTime($mandat.'-'.$month.'-'.$day));
            $em->persist($ph);

            //manage project manager
            $pm = new Personne();
            $pm->setPrenom(self::PRENOM[array_rand(self::PRENOM)]);
            $pm->setNom(self::NOM[array_rand(self::NOM)]);
            $pm->setEmailEstValide(false);
            $pm->setEstAbonneNewsletter(false);
            $em->persist($pm);
            $m = new Membre();
            $m->setPersonne($pm);
            $m->setPromotion($mandat + 2);
            $em->persist($m);
            if ($c !== null) {
                $c->addMembre($m);
            }
            $e->setSuiveur($pm);

            //manage AP & CC
            if ($etude['dateCC'] !== null && $etude['statut'] > 1) {
                $ap = new Ap();
                $ap->setEtude($e);
                $em->persist($ap);

                $cc = new Cc();
                $cc->setEtude($e);
                $cc->setDateSignature(new \DateTime($mandat.'-'.$month.'-'.$day));
                $cc->setSignataire2($pe);
                $em->persist($cc);
            }

            //manage intervenant
            if ($etude['statut'] > 1 && $etude['statut'] < 5) {
                //manage developper
                $dev = new Personne();
                $dev->setPrenom(self::PRENOM[array_rand(self::PRENOM)]);
                $dev->setNom(self::NOM[array_rand(self::NOM)]);
                $dev->setEmailEstValide(false);
                $dev->setEstAbonneNewsletter(false);
                $em->persist($dev);

                $mdev = new Membre();
                $mdev->setPersonne($dev);
                $mdev->setPromotion($mandat + rand(1, 2));
                $em->persist($mdev);
                if ($c !== null) {
                    $c->addMembre($mdev);
                }

                $mi = new Mission();
                $mi->setSignataire2($dev);
                $mi->setEtude($e);
                $mi->setDateSignature(new \DateTime($mandat.'-'.$month.'-'.$day));
                $mi->setDebutOm(new \DateTime($mandat.'-'.$month.'-'.$day));
                $mi->setAvancement(rand(90, 100));
                $mi->setIntervenant($mdev);

                $em->persist($mi);
            }

                //manage PVR
            if ($etude['statut'] > 1 && $etude['statut'] < 5) {
                $pv = new ProcesVerbal();
                $pv->setEtude($e);
                $pv->setDateSignature(new \DateTime($mandat.'-'.($month + 1).'-'.($day)));
                $pv->setSignataire2($pe);
                $pv->setType('pvr');
                $em->persist($pv);
            }
        }


        $em->flush();

        $output->writeln('Done.');
    }
}
