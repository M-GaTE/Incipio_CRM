<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\StatBundle\Controller;

use Mgate\StatBundle\Entity\Indicateur;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class IndicateursController extends Controller
{

    const STATE_ID_EN_COURS_X =2;
    const STATE_ID_TERMINEE_X = 4;

    const DEFAULT_STYLE = array('color' => '#000000', 'fontWeight' => 'bold', 'fontSize' => '16px');

    /**
     * @Security("has_role('ROLE_CA')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $indicateurs = $em->getRepository('MgateStatBundle:Indicateur')->findAll();
        $statsBrutes = array('Pas de données' => 'A venir');

        return $this->render('MgateStatBundle:Indicateurs:index.html.twig', array('indicateurs' => $indicateurs,
            'stats' => $statsBrutes,
        ));
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function debugAction($get)
    {
        $indicateur = new Indicateur();
        $indicateur->setTitre($get)
            ->setMethode($get);

        return $this->render('MgateStatBundle:Indicateurs:debug.html.twig', array('indicateur' => $indicateur,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    public function ajaxAction(Request $request)
    {
        if ($request->getMethod() == 'GET') {
            $chartMethode = $request->query->get('chartMethode');
            $em = $this->getDoctrine()->getManager();
            $indicateur = $em->getRepository('MgateStatBundle:Indicateur')->findOneByMethode($chartMethode);

            if ($indicateur !== null) {
                $method = $indicateur->getMethode();

                return $this->$method(); //okay, it's a little bit dirty ...
            }
        }

        return new Response('<!-- Chart '.$chartMethode.' does not exist. -->');
    }

/**
 * @Security("has_role('ROLE_CA')")
 */
    // NB On se base pas sur les numéro mais les dates de signature CC !
    private function getRetardParMandat()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();

        $Ccs = $em->getRepository('MgateSuiviBundle:Cc')->findBy(array(), array('dateSignature' => 'asc'));

        /* Initialisation */
        $nombreJoursParMandat = array();
        $nombreJoursAvecAvenantParMandat = array();

        $maxMandat = $etudeManager->getMaxMandatCc();

        for ($i = 0; $i <= $maxMandat; ++$i) {
            $nombreJoursParMandat[$i] = 0;
        }
        for ($i = 0; $i <= $maxMandat; ++$i) {
            $nombreJoursAvecAvenantParMandat[$i] = 0;
        }
        /*         * *************** */

        foreach ($Ccs as $cc) {
            $etude = $cc->getEtude();
            $dateSignature = $cc->getDateSignature();
            $signee = $etude->getStateID() == self::STATE_ID_EN_COURS_X
                || $etude->getStateID() == self::STATE_ID_TERMINEE_X;

            if ($dateSignature && $signee) {
                $idMandat = $etudeManager->dateToMandat($dateSignature);
                if ($etude->getDelai()) {
                    $nombreJoursParMandat[$idMandat] += $etude->getDelai(false)->days;
                    $nombreJoursAvecAvenantParMandat[$idMandat] += $etude->getDelai(true)->days;
                }
            }
        }

        $data = array();
        $categories = array();
        foreach ($nombreJoursParMandat as $idMandat => $datas) {
            if ($datas > 0) {
                $categories[] = $idMandat;
                $data[] = array('y' => 100 * ($nombreJoursAvecAvenantParMandat[$idMandat] - $datas) / $datas, 'nombreEtudes' => $datas, 'nombreEtudesAvecAv' => $nombreJoursAvecAvenantParMandat[$idMandat] - $datas);
            }
        }

        //create a new column chart with defaults already setted
        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = array(array('name' => 'Nombre de jour de retard / nombre de jour travaillés', 'colorByPoint' => true, 'data' => $data));
        $ob = $chartFactory->newColumnChart($series, $categories);

        //customize display
        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Retard par Mandat');
        $ob->yAxis->title(array('text' => 'Taux (%)', 'style' => self::DEFAULT_STYLE));
        $ob->yAxis->max(null);
        $ob->xAxis->title(array('text' => 'Mandat', 'style' => self::DEFAULT_STYLE));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('Les études ont duré en moyenne {point.y:.2f} % de plus que prévu<br/>avec {point.nombreEtudesAvecAv} jours de retard sur {point.nombreEtudes} jours travaillés');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

/**
 * @Security("has_role('ROLE_CA')")
 */
    // NB On se base pas sur les numéro mais les dates de signature CC !
    private function getNombreEtudes()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();

        $Ccs = $em->getRepository('MgateSuiviBundle:Cc')->findBy(array(), array('dateSignature' => 'asc'));

        /* Initialisation */
        $nombreEtudesParMandat = array();

        $maxMandat = $etudeManager->getMaxMandatCc();

        for ($i = 0; $i <= $maxMandat; ++$i) {
            $nombreEtudesParMandat[$i] = 0;
        }

        foreach ($Ccs as $cc) {
            $etude = $cc->getEtude();
            $dateSignature = $cc->getDateSignature();
            $signee = $etude->getStateID() == self::STATE_ID_EN_COURS_X
                || $etude->getStateID() == self::STATE_ID_TERMINEE_X;
            if ($dateSignature && $signee) {
                $idMandat = $etudeManager->dateToMandat($dateSignature);
                $nombreEtudesParMandat[$idMandat] += 1;
            }
        }
        $data = array();
        $categories = array();
        foreach ($nombreEtudesParMandat as $idMandat => $datas) {
            if ($datas > 0) {
                $categories[] = $idMandat;
                $data[] = array('y' => $datas);
            }
        }

        //create a column chart
        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = array(array('name' => "Nombre d'études par mandat", 'colorByPoint' => true, 'data' => $data));
        $ob = $chartFactory->newColumnChart($series, $categories);

        //set texts
        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Nombre d\'études par mandat');
        $ob->yAxis->max(null);
        $ob->yAxis->allowDecimals(false);
        $ob->yAxis->title(array('text' => 'Nombre', 'style' => self::DEFAULT_STYLE));
        $ob->xAxis->title(array('text' => 'Mandat', 'style' => self::DEFAULT_STYLE));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y} études');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getRepartitionSorties()
    {
        $em = $this->getDoctrine()->getManager();
        $mandat = $this->get('Mgate.etude_manager')->getMaxMandatCc();

        $nfs = $em->getRepository('MgateTresoBundle:NoteDeFrais')->findBy(array('mandat' => $mandat));
        $bvs = $em->getRepository('MgateTresoBundle:BV')->findBy(array('mandat' => $mandat));

        /* Initialisation */
        $comptes = array();
        $comptes['Honoraires BV'] = 0;
        $comptes['URSSAF'] = 0;
        $montantTotal = 1; //set to 1 to avoid division by 0 in case montantTotal equals 0.
        foreach ($nfs as $nf) {
            foreach ($nf->getDetails() as $detail) {
                $compte = $detail->getCompte();
                if ($compte !== null) {
                    $compte = $detail->getCompte()->getLibelle();
                    $montantTotal += $detail->getMontantHT();
                    if (array_key_exists($compte, $comptes)) {
                        $comptes[$compte] += $detail->getMontantHT();
                    } else {
                        $comptes[$compte] = $detail->getMontantHT();
                    }
                }
            }
        }

        foreach ($bvs as $bv) {
            $comptes['Honoraires BV'] += $bv->getRemunerationBrute();
            $comptes['URSSAF'] += $bv->getPartJunior();
            $montantTotal += $bv->getRemunerationBrute() + $bv->getPartJunior();
        }

        ksort($comptes);
        $data = array();
        foreach ($comptes as $compte => $montantHT) {
            $data[] = array($compte, 100 * $montantHT / $montantTotal);
        }

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $series = array(array('type' => 'pie', 'name' => 'Répartition des dépenses', 'data' => $data, 'Dépenses totale' => $montantTotal));
        $ob = $chartFactory->newPieChart($series);
        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Répartition des dépenses selon les comptes comptables (Mandat en cours)');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getSortie()
    {
        $em = $this->getDoctrine()->getManager();

        $sortiesParMandat = $em->getRepository('MgateTresoBundle:NoteDeFrais')->findAllByMandat();
        $bvsParMandat = $em->getRepository('MgateTresoBundle:BV')->findAllByMandat();

        $data = array();
        $categories = array();

        $comptes = array();
        $comptes['Honoraires BV'] = array();
        $comptes['URSSAF'] = array();
        $mandats = array();
        ksort($sortiesParMandat); // Trie selon les mandats
        foreach ($sortiesParMandat as $mandat => $nfs) { // Pour chaque Mandat
            $mandats[] = $mandat;
            foreach ($nfs as $nf) { // Pour chaque NF d'un mandat
                foreach ($nf->getDetails() as $detail) { // Pour chaque détail d'une NF
                    $compte = $detail->getCompte();
                    if ($compte !== null) {
                        $compte = $detail->getCompte()->getLibelle();
                        if (array_key_exists($compte, $comptes)) {
                            if (array_key_exists($mandat, $comptes[$compte])) {
                                $comptes[$compte][$mandat] += $detail->getMontantHT();
                            } else {
                                $comptes[$compte][$mandat] = $detail->getMontantHT();
                            }
                        } else {
                            $comptes[$compte] = array($mandat => $detail->getMontantHT());
                        }
                    }
                }
            }
        }
        foreach ($bvsParMandat as $mandat => $bvs) { // Pour chaque Mandat
            if (!in_array($mandat, $mandats)) {
                $mandats[] = $mandat;
            }
            $comptes['Honoraires BV'][$mandat] = 0;
            $comptes['URSSAF'][$mandat] = 0;
            foreach ($bvs as $bv) {
                // Pour chaque BV d'un mandat
                $comptes['Honoraires BV'][$mandat] += $bv->getRemunerationBrute();
                $comptes['URSSAF'][$mandat] += $bv->getPartJunior();
            }
        }

        $series = array();
        ksort($mandats);
        ksort($comptes);
        foreach ($comptes as $libelle => $compte) {
            $data = array();
            foreach ($mandats as $mandat) {
                if (array_key_exists($mandat, $compte)) {
                    $data[] = (float) $compte[$mandat];
                } else {
                    $data[] = 0;
                }
            }
            $series[] = array('name' => $libelle, 'data' => $data);
        }

        foreach ($mandats as $mandat) {
            $categories[] = 'Mandat '.$mandat;
        }

        //chart
        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $ob = $chartFactory->newColumnChart($series, $categories);
        $ob->plotOptions->column(array('stacking' => 'normal'));

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Montant HT des dépenses');
        $ob->yAxis->title(array('text' => 'Montant (€)', 'style' => self::DEFAULT_STYLE));
        $ob->yAxis->max(null);
        $ob->xAxis->title(array('text' => 'Mandat', 'style' => self::DEFAULT_STYLE));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y} € HT');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getPartClientFidel()
    {
        $em = $this->getDoctrine()->getManager();
        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        $clients = array();
        foreach ($etudes as $etude) {
            if ($etude->getStateID() == self::STATE_ID_EN_COURS_X || $etude->getStateID() == self::STATE_ID_TERMINEE_X) {
                $clientID = $etude->getProspect()->getId();
                if (key_exists($clientID, $clients)) {
                    ++$clients[$clientID];
                } else {
                    $clients[$clientID] = 1;
                }
            }
        }

        $repartitions = array();
        $nombreClient = count($clients);
        foreach ($clients as $clientID => $nombreEtude) {
            if (key_exists($nombreEtude, $repartitions)) {
                ++$repartitions[$nombreEtude];
            } else {
                $repartitions[$nombreEtude] = 1;
            }
        }

        /* Initialisation */
        $data = array();
        ksort($repartitions);
        foreach ($repartitions as $occ => $nbr) {
            $data[] = array($occ == 1 ? "$nbr Nouveaux clients" : "$nbr Anciens clients ($occ études)", 100 * $nbr / $nombreClient);
        }

        $series = array(array('type' => 'pie', 'name' => 'Taux de fidélisation', 'data' => $data, 'Nombre de client' => $nombreClient));

        $ob = new Highchart();
        $ob->chart->renderTo(__FUNCTION__);
        // Plot Options
        $ob->plotOptions->pie(array('allowPointSelect' => true, 'cursor' => 'pointer', 'showInLegend' => true, 'dataLabels' => array('enabled' => false)));

        $ob->series($series);
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->credits->enabled(false);
        $ob->title->text('Taux de fidélisation (% de clients ayant demandé plusieurs études)');
        $ob->tooltip->pointFormat('{point.percentage:.1f} %');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getNombreDePresentFormationsTimed()
    {
        $em = $this->getDoctrine()->getManager();
        $formationsParMandat = $em->getRepository('MgateFormationBundle:Formation')->findAllByMandat();

        $maxMandat = max(array_keys($formationsParMandat));
        $mandats = array();

        foreach ($formationsParMandat as $mandat => $formations) {
            foreach ($formations as $formation) {
                if ($formation->getDateDebut()) {
                    $interval = new \DateInterval('P'.($maxMandat - $mandat).'Y');
                    $dateDecale = clone $formation->getDateDebut();
                    $dateDecale->add($interval);
                    $mandats[$mandat][] = array(
                        'x' => $dateDecale->getTimestamp() * 1000,
                        'y' => count($formation->getMembresPresents()), 'name' => $formation->getTitre(),
                        'date' => $dateDecale->format('d/m/Y'),
                    );
                }
            }
        }

        $series = array();
        foreach ($mandats as $mandat => $data) {
            $series[] = array('name' => 'Mandat '.$mandat, 'data' => $data);
        }

        $ob = new Highchart();
        $ob->chart->renderTo(__FUNCTION__);
        // OTHERS
        $ob->global->useUTC(false);

        /*
         * DATAS
         */
        $ob->series($series);
        $ob->xAxis->type('datetime');
        $ob->xAxis->dateTimeLabelFormats(array('month' => '%b'));

        $ob->yAxis->min(0);
        $ob->yAxis->allowDecimals(false);
        $style = array('color' => '#000000', 'fontWeight' => 'bold', 'fontSize' => '16px');
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->xAxis->labels(array('style' => self::DEFAULT_STYLE));
        $ob->yAxis->labels(array('style' => self::DEFAULT_STYLE));
        $ob->credits->enabled(false);
        $ob->legend->enabled(false);

        /*
         * TEXTS AND LABELS
         */
        $ob->title->text('Nombre de présents aux formations');
        $ob->yAxis->title(array('text' => 'Nombre de présents', 'style' => self::DEFAULT_STYLE));
        $ob->xAxis->title(array('text' => 'Date', 'style' => self::DEFAULT_STYLE));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y} présent le {point.date}<br />{point.name}');
        $ob->legend->layout('vertical');
        $ob->legend->y(40);
        $ob->legend->x(90);
        $ob->legend->verticalAlign('top');
        $ob->legend->reversed(true);
        $ob->legend->align('left');
        $ob->legend->backgroundColor('#FFFFFF');
        $ob->legend->itemStyle($style);
        $ob->plotOptions->series(array('lineWidth' => 5, 'marker' => array('radius' => 8)));

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getNombreFormationsParMandat()
    {
        $em = $this->getDoctrine()->getManager();

        $formationsParMandat = $em->getRepository('MgateFormationBundle:Formation')->findAllByMandat();

        $data = array();
        $categories = array();

        ksort($formationsParMandat); // Tire selon les promos
        foreach ($formationsParMandat as $mandat => $formations) {
            $data[] = count($formations);
            $categories[] = 'Mandat '.$mandat;
        }
        $series = array(array('name' => 'Nombre de formations', 'colorByPoint' => true, 'data' => $data));

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $ob = $chartFactory->newColumnChart($series, $categories);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Nombre de formations théorique par mandat');
        $ob->yAxis->title(array('text' => 'Nombre de formations', 'style' => self::DEFAULT_STYLE));
        $ob->yAxis->max(null);
        $ob->xAxis->title(array('text' => 'Mandat', 'style' => self::DEFAULT_STYLE));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y}');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

/**
 * @Security("has_role('ROLE_CA')")
 */
    // NB On se base pas sur les numéro mais les dates de signature CC !
    private function getTauxDAvenantsParMandat()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();

        $Ccs = $em->getRepository('MgateSuiviBundle:Cc')->findBy(array(), array('dateSignature' => 'asc'));

        /* Initialisation */
        $nombreEtudesParMandat = array();
        $nombreEtudesAvecAvenantParMandat = array();

        $maxMandat = $etudeManager->getMaxMandatCc();

        for ($i = 0; $i <= $maxMandat; ++$i) {
            $nombreEtudesParMandat[$i] = 0;
        }
        for ($i = 0; $i <= $maxMandat; ++$i) {
            $nombreEtudesAvecAvenantParMandat[$i] = 0;
        }
        /*         * *************** */

        foreach ($Ccs as $cc) {
            $etude = $cc->getEtude();
            $dateSignature = $cc->getDateSignature();
            $signee = $etude->getStateID() == self::STATE_ID_EN_COURS_X
                || $etude->getStateID() == self::STATE_ID_TERMINEE_X;

            if ($dateSignature && $signee) {
                $idMandat = $etudeManager->dateToMandat($dateSignature);

                ++$nombreEtudesParMandat[$idMandat];
                if (count($etude->getAvs()->toArray())) {
                    ++$nombreEtudesAvecAvenantParMandat[$idMandat];
                }
            }
        }

        $data = array();
        $categories = array();
        foreach ($nombreEtudesParMandat as $idMandat => $datas) {
            if ($datas > 0) {
                $categories[] = $idMandat;
                $data[] = array('y' => 100 * $nombreEtudesAvecAvenantParMandat[$idMandat] / $datas, 'nombreEtudes' => $datas, 'nombreEtudesAvecAv' => $nombreEtudesAvecAvenantParMandat[$idMandat]);
            }
        }
        $series = array(array('name' => "Taux d'avenant par Mandat", 'colorByPoint' => true, 'data' => $data));

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $ob = $chartFactory->newColumnChart($series, $categories);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Taux d\'avenant par Mandat');
        $ob->yAxis->title(array('text' => 'Taux (%)', 'style' => self::DEFAULT_STYLE));
        $ob->xAxis->title(array('text' => 'Mandat', 'style' => self::DEFAULT_STYLE));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y:.2f} %<br/>avec {point.nombreEtudesAvecAv} sur {point.nombreEtudes} études');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getRepartitionClientSelonChiffreAffaire()
    {
        $em = $this->getDoctrine()->getManager();
        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        $chiffreDAffairesTotal = 0;

        $repartitions = array();

        foreach ($etudes as $etude) {
            if ($etude->getStateID() == self::STATE_ID_EN_COURS_X || $etude->getStateID() == self::STATE_ID_TERMINEE_X) {
                $type = $etude->getProspect()->getEntiteToString();
                $CA = $etude->getMontantHT();
                $chiffreDAffairesTotal += $CA;
                array_key_exists($type, $repartitions) ? $repartitions[$type] += $CA : $repartitions[$type] = $CA;
            }
        }

        $data = array();
        foreach ($repartitions as $type => $CA) {
            if ($type === null) {
                $type = 'Autre';
            }
            $data[] = array($type, round($CA / $chiffreDAffairesTotal * 100, 2));
        }

        $series = array(array('type' => 'pie', 'name' => 'Provenance de nos études par type de Client (tous mandats)', 'data' => $data, 'CA Total' => $chiffreDAffairesTotal));

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $ob = $chartFactory->newPieChart($series);
        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text("Répartition du CA selon le type de Client ($chiffreDAffairesTotal € CA)");

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getRepartitionClientParNombreDEtude()
    {
        $em = $this->getDoctrine()->getManager();
        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        $nombreClient = 0;
        $repartitions = array();

        foreach ($etudes as $etude) {
            if ($etude->getStateID() == self::STATE_ID_EN_COURS_X || $etude->getStateID() == self::STATE_ID_TERMINEE_X) {
                ++$nombreClient;
                $type = $etude->getProspect()->getEntiteToString();
                array_key_exists($type, $repartitions) ? $repartitions[$type]++ : $repartitions[$type] = 1;
            }
        }

        $data = array();
        $categories = array();
        foreach ($repartitions as $type => $nombre) {
            if ($type === null) {
                $type = 'Autre';
            }
            $data[] = array($type, round($nombre / $nombreClient * 100, 2));
        }

        $series = array(array('type' => 'pie', 'name' => 'Provenance des études par type de Client (tous mandats)', 'data' => $data, 'nombreClient' => $nombreClient));

        $ob = new Highchart();
        $ob->chart->renderTo(__FUNCTION__);
        // Plot Options
        $ob->plotOptions->pie(array('allowPointSelect' => true, 'cursor' => 'pointer', 'showInLegend' => true, 'dataLabels' => array('enabled' => false)));
        $ob->series($series);
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->credits->enabled(false);
        $ob->title->text('Provenance des études par type de Client ('.$nombreClient.' Etudes)');
        $ob->tooltip->pointFormat('{point.percentage:.1f} %');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    private function cmp($a, $b)
    {
        if ($a['date'] == $b['date']) {
            return 0;
        }

        return ($a['date'] < $b['date']) ? -1 : 1;
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getNombreMembres()
    {
        $em = $this->getDoctrine()->getManager();
        $mandats = $em->getRepository('MgatePersonneBundle:Mandat')->getCotisantMandats();

        $promos = array();
        $cumuls = array();
        $dates = array();
        foreach ($mandats as $mandat) {
            if ($membre = $mandat->getMembre()) {
                $p = $membre->getPromotion();
                if (!in_array($p, $promos)) {
                    $promos[] = $p;
                }
                $dates[] = array('date' => $mandat->getDebutMandat(), 'type' => '1', 'promo' => $p);
                $dates[] = array('date' => $mandat->getFinMandat(), 'type' => '-1', 'promo' => $p);
            }
        }
        sort($promos);
        usort($dates, array($this, 'cmp'));

        foreach ($dates as $date) {
            $d = $date['date']->format('m/y');
            $p = $date['promo'];
            $t = $date['type'];
            foreach ($promos as $promo) {
                if (!array_key_exists($promo, $cumuls)) {
                    $cumuls[$promo] = array();
                }
                $cumuls[$promo][$d] = (array_key_exists($d, $cumuls[$promo]) ? $cumuls[$promo][$d] : (end($cumuls[$promo]) ? end($cumuls[$promo]) : 0));
            }
            $cumuls[$p][$d] += $t;
        }

        $series = array();
        $categories = array_keys($cumuls[$promos[0]]);
        foreach (array_reverse($promos) as $promo) {
            $series[] = array('name' => 'P'.$promo, 'data' => array_values($cumuls[$promo]));
        }

        $ob = new Highchart();
        $ob->chart->renderTo(__FUNCTION__);
        // OTHERS
        $ob->chart->type('area');
        $ob->chart->zoomType('x');
        $ob->plotOptions->area(array('stacking' => 'normal'));

        $ob->series($series);
        $ob->xAxis->categories($categories);

        $ob->yAxis->min(0);
        $ob->yAxis->allowDecimals(false);
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->xAxis->labels(array('style' => self::DEFAULT_STYLE, 'rotation' => -45));
        $ob->yAxis->labels(array('style' => self::DEFAULT_STYLE));
        $ob->credits->enabled(false);

        $ob->title->text('Nombre de membre');
        $ob->yAxis->title(array('text' => 'Nombre de membre', 'style' => self::DEFAULT_STYLE));
        $ob->xAxis->title(array('text' => 'Promotion', 'style' => self::DEFAULT_STYLE));
        $ob->tooltip->shared(true);
        $ob->tooltip->valueSuffix(' cotisants');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getMembresParPromo()
    {
        $em = $this->getDoctrine()->getManager();
        $membres = $em->getRepository('MgatePersonneBundle:Membre')->findAll();

        $promos = array();

        foreach ($membres as $membre) {
            $p = $membre->getPromotion();
            if ($p) {
                array_key_exists($p, $promos) ? $promos[$p]++ : $promos[$p] = 1;
            }
        }

        $data = array();
        $categories = array();

        ksort($promos); // Tire selon les promos
        foreach ($promos as $promo => $nombre) {
            $data[] = $nombre;
            $categories[] = 'P'.$promo;
        }
        $series = array(array('name' => 'Membres', 'colorByPoint' => true, 'data' => $data));

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $ob = $chartFactory->newColumnChart($series, $categories);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Nombre de membres par Promotion');
        $ob->yAxis->title(array('text' => 'Nombre de membres', 'style' => self::DEFAULT_STYLE));
        $ob->yAxis->max(null);
        $ob->xAxis->title(array('text' => 'Promotion', 'style' => self::DEFAULT_STYLE));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y}');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getIntervenantsParPromo()
    {
        $em = $this->getDoctrine()->getManager();
        $intervenants = $em->getRepository('MgatePersonneBundle:Membre')->getIntervenantsParPromo();

        $promos = array();

        foreach ($intervenants as $intervenant) {
            $p = $intervenant->getPromotion();
            if ($p) {
                array_key_exists($p, $promos) ? $promos[$p]++ : $promos[$p] = 1;
            }
        }

        $data = array();
        $categories = array();
        foreach ($promos as $promo => $nombre) {
            $data[] = $nombre;
            $categories[] = 'P'.$promo;
        }
        $series = array(array('name' => 'Intervenants', 'colorByPoint' => true, 'data' => $data));

        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $ob = $chartFactory->newColumnChart($series, $categories);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Nombre d\'intervenants par Promotion');
        $ob->yAxis->title(array('text' => "Nombre d'intervenants", 'style' => self::DEFAULT_STYLE));
        $ob->yAxis->max(null);
        $ob->xAxis->title(array('text' => 'Promotion', 'style' => self::DEFAULT_STYLE));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y}');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getCAM()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $em = $this->getDoctrine()->getManager();

        $Ccs = $em->getRepository('MgateSuiviBundle:Cc')->findBy(array(), array('dateSignature' => 'asc'));

        /* Initialisation */
        $cumuls = array();
        $cumulsJEH = array();
        $cumulsFrais = array();

        $maxMandat = $etudeManager->getMaxMandatCc();

        for ($i = 0; $i <= $maxMandat; ++$i) {
            $cumuls[$i] = 0;
        }
        for ($i = 0; $i <= $maxMandat; ++$i) {
            $cumulsJEH[$i] = 0;
        }
        for ($i = 0; $i <= $maxMandat; ++$i) {
            $cumulsFrais[$i] = 0;
        }
        /*         * *************** */

        foreach ($Ccs as $cc) {
            $etude = $cc->getEtude();
            $dateSignature = $cc->getDateSignature();
            $signee = $etude->getStateID() == self::STATE_ID_EN_COURS_X
                || $etude->getStateID() == self::STATE_ID_TERMINEE_X;

            if ($dateSignature && $signee) {
                $idMandat = $etudeManager->dateToMandat($dateSignature);

                $cumuls[$idMandat] += $etude->getMontantHT();
                $cumulsJEH[$idMandat] += $etude->getNbrJEH();
                $cumulsFrais[$idMandat] += $etude->getFraisDossier();
            }
        }

        $data = array();
        $categories = array();
        foreach ($cumuls as $idMandat => $datas) {
            if ($datas > 0) {
                $categories[] = $idMandat;
                $data[] = array('y' => $datas, 'JEH' => $cumulsJEH[$idMandat], 'moyJEH' => ($datas - $cumulsFrais[$idMandat]) / $cumulsJEH[$idMandat]);
            }
        }

        $series = array(
            array(
                'name' => 'CA Signé',
                'colorByPoint' => true,
                'data' => $data,
                'dataLabels' => array(
                    'enabled' => true,
                    'rotation' => -90,
                    'align' => 'right',
                    'format' => '{point.y} €',
                    'style' => array(
                        'color' => '#FFFFFF',
                        'fontSize' => '20px',
                        'fontFamily' => 'Verdana, sans-serif',
                        'textShadow' => '0 0 3px black', ),
                    'y' => 25,

                ),
            ),
        );
        $chartFactory = $this->container->get('Mgate_stat.chart_factory');
        $ob = $chartFactory->newColumnChart($series, $categories);

        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Évolution du chiffre d\'affaires signé cumulé par mandat');
        $ob->yAxis->title(array('text' => 'CA (€)', 'style' => self::DEFAULT_STYLE));
        $ob->yAxis->max(null);
        $ob->xAxis->title(array('text' => 'Mandat', 'style' => self::DEFAULT_STYLE));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y} €<br/>en {point.JEH} JEH<br/>soit {point.moyJEH:.2f} €/JEH');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /*
     *  REGION OLD
     */

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getCA()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $Ccs = $this->getDoctrine()->getManager()->getRepository('MgateSuiviBundle:Cc')->findBy(array(), array('dateSignature' => 'asc'));

        $mandats = array();
        $maxMandat = $etudeManager->getMaxMandatCc();

        $cumuls = array();
        for ($i = 0; $i <= $maxMandat; ++$i) {
            $cumuls[$i] = 0;
        }

        foreach ($Ccs as $cc) {
            $etude = $cc->getEtude();
            $dateSignature = $cc->getDateSignature();
            $signee = $etude->getStateID() == self::STATE_ID_EN_COURS_X
                || $etude->getStateID() == self::STATE_ID_TERMINEE_X;

            if ($dateSignature && $signee) {
                $idMandat = $etudeManager->dateToMandat($dateSignature);

                $cumuls[$idMandat] += $etude->getMontantHT();

                $interval = new \DateInterval('P'.($maxMandat - $idMandat).'Y');
                $dateDecale = clone $dateSignature;
                $dateDecale->add($interval);

                $mandats[$idMandat][]
                    = array('x' => $dateDecale->getTimestamp() * 1000,
                    'y' => $cumuls[$idMandat], 'name' => $etude->getReference().' - '.$etude->getNom(),
                    'date' => $dateDecale->format('d/m/Y'),
                    'prix' => $etude->getMontantHT(), );
            }
        }

        // Chart
        $series = array();
        foreach ($mandats as $idMandat => $data) {
            //if($idMandat>=4)
            $series[] = array('name' => 'Mandat '.$etudeManager->mandatToString($idMandat), 'data' => $data);
        }

        $style = array('color' => '#000000', 'fontWeight' => 'bold', 'fontSize' => '16px');

        $ob = new Highchart();
        $ob->global->useUTC(false);

        $ob->chart->renderTo(__FUNCTION__);  // The #id of the div where to render the chart
        $ob->xAxis->labels(array('style' => self::DEFAULT_STYLE));
        $ob->yAxis->labels(array('style' => self::DEFAULT_STYLE));
        $ob->title->text('Évolution par mandat du chiffre d\'affaire signé cumulé');
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->xAxis->title(array('text' => 'Date', 'style' => self::DEFAULT_STYLE));
        $ob->xAxis->type('datetime');
        $ob->xAxis->dateTimeLabelFormats(array('month' => '%b'));
        $ob->yAxis->min(0);
        $ob->yAxis->title(array('text' => "Chiffre d'Affaire signé cumulé", 'style' => self::DEFAULT_STYLE));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->tooltip->pointFormat('{point.y} le {point.date}<br />{point.name} à {point.prix} €');
        $ob->credits->enabled(false);
        $ob->legend->floating(false);
        $ob->legend->layout('vertical');
        $ob->legend->y(-60);
        $ob->legend->x(-10);
        $ob->legend->verticalAlign('bottom');
        $ob->legend->reversed(true);
        $ob->legend->align('right');
        $ob->legend->backgroundColor('#F6F6F6');
        $ob->legend->itemStyle($style);
        $ob->plotOptions->series(
            array(
                'lineWidth' => 3,
                'marker' => array('radius' => 6),
            )
        );

        $ob->series($series);

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getRh()
    {
        $etudeManager = $this->get('Mgate.etude_manager');
        $missions = $this->getDoctrine()->getManager()->getRepository('MgateSuiviBundle:Mission')->findBy(array(), array('debutOm' => 'asc'));

        $mandats = array();
        $maxMandat = $etudeManager->getMaxMandatCc();

        $cumuls = array();
        for ($i = 0; $i <= $maxMandat; ++$i) {
            $cumuls[$i] = 0;
        }

        $mandats[1] = array();

        //Etape 1 remplir toutes les dates
        foreach ($missions as $mission) {
            $etude = $mission->getEtude();
            $dateDebut = $mission->getdebutOm();
            $dateFin = $mission->getfinOm();

            if ($dateDebut && $dateFin) {
                $idMandat = $etudeManager->dateToMandat($dateDebut);

                ++$cumuls[0];

                $dateDebutDecale = clone $dateDebut;
                $dateFinDecale = clone $dateFin;

                $addDebut = true;
                $addFin = true;
                foreach ($mandats[1] as $datePoint) {
                    if (($dateDebutDecale->getTimestamp() * 1000) == $datePoint['x']) {
                        $addDebut = false;
                    }
                    if (($dateFinDecale->getTimestamp() * 1000) == $datePoint['x']) {
                        $addFin = false;
                    }
                }

                if ($addDebut) {
                    $mandats[1][]
                        = array('x' => $dateDebutDecale->getTimestamp() * 1000,
                        'y' => 0/* $cumuls[0] */, 'name' => $etude->getReference().' + '.$etude->getNom(),
                        'date' => $dateDebutDecale->format('d/m/Y'),
                        'prix' => $etude->getMontantHT(), );
                }
                if ($addFin) {
                    $mandats[1][]
                        = array('x' => $dateFinDecale->getTimestamp() * 1000,
                        'y' => 0/* $cumuls[0] */, 'name' => $etude->getReference().' - '.$etude->getNom(),
                        'date' => $dateDebutDecale->format('d/m/Y'),
                        'prix' => $etude->getMontantHT(), );
                }
            }
        }

        //Etapes 2 trie dans l'ordre
        $callback = function ($a, $b) use ($mandats) {
            return $mandats[1][$a]['x'] > $mandats[1][$b]['x'];
        };
        uksort($mandats[1], $callback);
        foreach ($mandats[1] as $entree) {
            $mandats[2][] = $entree;
        }
        $mandats[1] = array();

        //Etapes 3 ++ --
        foreach ($missions as $mission) {
            $etude = $mission->getEtude();
            $dateFin = $mission->getfinOm();
            $dateDebut = $mission->getdebutOm();

            if ($dateDebut && $dateFin) {
                $dateDebutDecale = clone $dateDebut;
                $dateFinDecale = clone $dateFin;

                foreach ($mandats[2] as &$entree) {
                    if ($entree['x'] >= $dateDebutDecale->getTimestamp() * 1000 && $entree['x'] < $dateFinDecale->getTimestamp() * 1000) {
                        ++$entree['y'];
                    }
                }
            }
        }

        // Chart
        $series = array();
        foreach ($mandats as $idMandat => $data) {
            //if($idMandat>=4)
            $series[] = array('name' => 'Mandat '.$idMandat.' - '.$etudeManager->mandatToString($idMandat), 'data' => $data);
        }

        $style = array('color' => '#000000', 'fontWeight' => 'bold', 'fontSize' => '16px');

        $ob = new Highchart();
        $ob->global->useUTC(false);

        //WARN :::

        $ob->chart->renderTo('getRh');  // The #id of the div where to render the chart
        ///
        $ob->chart->type('spline');
        $ob->xAxis->labels(array('style' => self::DEFAULT_STYLE));
        $ob->yAxis->labels(array('style' => self::DEFAULT_STYLE));
        $ob->title->text("Évolution par mandat du nombre d'intervenant");
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->xAxis->title(array('text' => 'Date', 'style' => self::DEFAULT_STYLE));
        $ob->xAxis->type('datetime');
        $ob->xAxis->dateTimeLabelFormats(array('month' => '%b'));
        $ob->yAxis->min(0);
        $ob->yAxis->title(array('text' => "Nombre d'intervenant", 'style' => self::DEFAULT_STYLE));
        $ob->tooltip->headerFormat('<b>{series.name}</b><br />');
        $ob->credits->enabled(false);
        $ob->legend->floating(true);
        $ob->legend->layout('vertical');
        $ob->legend->y(40);
        $ob->legend->x(90);
        $ob->legend->verticalAlign('top');
        $ob->legend->reversed(true);
        $ob->legend->align('left');
        $ob->legend->backgroundColor('#FFFFFF');
        $ob->legend->itemStyle($style);
        $ob->plotOptions->series(array('lineWidth' => 5, 'marker' => array('radius' => 8)));
        $ob->series($series);

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getSourceProspectionParNombreDEtude()
    {
        $em = $this->getDoctrine()->getManager();
        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        $nombreClient = 0;
        $repartitions = array();

        foreach ($etudes as $etude) {
            if ($etude->getStateID() == self::STATE_ID_EN_COURS_X || $etude->getStateID() == self::STATE_ID_TERMINEE_X) {
                ++$nombreClient;
                $type = $etude->getSourceDeProspectionToString();
                array_key_exists($type, $repartitions) ? $repartitions[$type]++ : $repartitions[$type] = 1;
            }
        }

        $data = array();
        foreach ($repartitions as $type => $nombre) {
            if ($type === null) {
                $type = 'Autre';
            }
            $data[] = array($type, round($nombre / $nombreClient * 100, 2));
        }

        $series = array(array('type' => 'pie', 'name' => 'Provenance des études par source de prospection (tous mandats)', 'data' => $data, 'nombreClient' => $nombreClient));

        $ob = new Highchart();
        $ob->chart->renderTo(__FUNCTION__);
        // Plot Options
        $ob->plotOptions->pie(array('allowPointSelect' => true, 'cursor' => 'pointer', 'showInLegend' => true, 'dataLabels' => array('enabled' => false)));
        $ob->series($series);
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->credits->enabled(false);
        $ob->title->text("Provenance des études par source de prospection ($nombreClient Etudes)");
        $ob->tooltip->pointFormat('{point.percentage:.1f} %');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     */
    private function getSourceProspectionSelonChiffreAffaire()
    {
        $em = $this->getDoctrine()->getManager();
        $etudes = $em->getRepository('MgateSuiviBundle:Etude')->findAll();

        $chiffreDAffairesTotal = 0;

        $repartitions = array();

        foreach ($etudes as $etude) {
            if ($etude->getStateID() == self::STATE_ID_EN_COURS_X || $etude->getStateID() == self::STATE_ID_TERMINEE_X) {
                $type = $etude->getSourceDeProspectionToString();
                $CA = $etude->getMontantHT();
                $chiffreDAffairesTotal += $CA;
                array_key_exists($type, $repartitions) ? $repartitions[$type] += $CA : $repartitions[$type] = $CA;
            }
        }

        $data = array();
        foreach ($repartitions as $type => $CA) {
            if ($type === null) {
                $type = 'Autre';
            }
            $data[] = array($type, round($CA / $chiffreDAffairesTotal * 100, 2));
        }

        $series = array(array('type' => 'pie', 'name' => 'Provenance de nos études par type de Client (tous mandats)', 'data' => $data, 'CA Total' => $chiffreDAffairesTotal));

        $ob = new Highchart();
        $ob->chart->renderTo(__FUNCTION__);
        // Plot Options
        $ob->plotOptions->pie(array('allowPointSelect' => true, 'cursor' => 'pointer', 'showInLegend' => true, 'dataLabels' => array('enabled' => false)));

        $ob->series($series);
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->credits->enabled(false);
        $ob->title->text("Répartition du CA selon la source de prospection ($chiffreDAffairesTotal € CA)");
        $ob->tooltip->pointFormat('{point.percentage:.1f} %');

        return $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }

    /**
     * @Security("has_role('ROLE_CA')")
     * A chart displaying how much a skill has brought in turnover
     */
    private function getCACompetences()
    {
        $etude_manager = $this->get('Mgate.etude_manager');
        $MANDAT_MAX = $etude_manager->getMaxMandat();
        $MANDAT_MIN = $etude_manager->getMinMandat();

        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository('N7consultingRhBundle:Competence')->getAllEtudesByCompetences();

        //how much each skill has make us earn.
        $series = array();
        $categories = array();
        $used_mandats = array_fill(0, $MANDAT_MAX - $MANDAT_MIN + 1, 0); // an array to post-process results and remove mandats without data.
        //create array structure
        foreach ($res as $c) {
            $temp = array(
              'name' => $c->getNom(),
              'data' => array_fill(0, $MANDAT_MAX - $MANDAT_MIN + 1, 0),
            );

            $sumSkill = 0;
            foreach ($c->getEtudes() as $e) {
                $temp['data'][$e->getMandat() - $MANDAT_MIN] += $e->getMontantHT();
                $used_mandats[$e->getMandat() - $MANDAT_MIN] += 1;
                $sumSkill += $e->getMontantHT();
            }
            if ($sumSkill > 0) {
                $series[] = $temp;
            }
        }

        for ($i = $MANDAT_MIN; $i <= $MANDAT_MAX; ++$i) {
            $categories[] = 'Mandat '.$i;
        }

        //remove mandats with no skills used
        //once array has been spliced, index will be changed. Therefore, we uses $k has read index
        $k = 0;
        for ($i = 0; $i <= $MANDAT_MAX - $MANDAT_MIN; ++$i) {
            if ($used_mandats[$i] == 0 && isset($categories[$k])) {
                array_splice($categories, $k, 1);
                $count_series = count($series);
                for ($j = 0; $j < $count_series; ++$j) {
                    array_splice($series[$j]['data'], $k, 1);
                }
            } else {
                ++$k;
            }
        }

        $ob = new Highchart();
        // ID de l'élement de DOM que vous utilisez comme conteneur
        $ob->chart->renderTo(__FUNCTION__);
        $ob->title->text('Revenus par compétences');
        $ob->chart->type('column');

        $ob->plotOptions->pie(array('allowPointSelect' => true, 'cursor' => 'pointer', 'showInLegend' => true, 'dataLabels' => array('enabled' => false)));
        $ob->title->style(array('fontWeight' => 'bold', 'fontSize' => '20px'));
        $ob->credits->enabled(false);
        $ob->yAxis->title(array('text' => 'Revenus par compétences'));
        $ob->xAxis->title(array('text' => 'Mandats'));
        $ob->xAxis->categories($categories);
        $ob->series($series);

        return  $this->render('MgateStatBundle:Indicateurs:Indicateur.html.twig', array(
            'chart' => $ob,
        ));
    }
}
