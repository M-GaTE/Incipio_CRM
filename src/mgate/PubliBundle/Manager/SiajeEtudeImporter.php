<?php

namespace mgate\PubliBundle\Manager;

use Doctrine\ORM\EntityManager;
use mgate\PersonneBundle\Entity\Employe;
use mgate\PersonneBundle\Entity\Membre;
use mgate\PersonneBundle\Entity\Personne;
use mgate\PersonneBundle\Entity\Prospect;
use mgate\SuiviBundle\Entity\Ap;
use mgate\SuiviBundle\Entity\Cc;
use mgate\SuiviBundle\Entity\Etude;
use mgate\SuiviBundle\Entity\GroupePhases;
use mgate\SuiviBundle\Entity\Phase;
use mgate\SuiviBundle\Entity\ProcesVerbal;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * Class SiajeImporter
 * @package mgate\PubliBundle\Manager
 * Service that imports from
 */
class SiajeEtudeImporter extends CsvImporter implements FileImporterInterface
{

    const EXPECTED_FORMAT = array('No Etude', 'Exercice comptable', 'Intitule', 'Statut', 'Domaine de compétence', 'Montant HT', 'Frais de dossier HT', 'Frais variables', 'Acompte', 'JEHs', 'Durée en semaine', 'Suiveur principal', 'Suiveur qualité', 'Contact', 'Email', 'Entreprise', 'Adresse', 'Code Postal', 'Ville', 'Provenance', 'Progression', 'Date d\'ajout', 'Date d\'édition', 'Date d\'envoi du devis', 'Date signature CC', 'Date signature PV', 'Date de cloturation', 'Date de mise en standby', 'Date de signature projetée', 'Date d\'avortement',);

    //link between siaje string for state and our stateID integer. Slugified
    const SIAJE_AVAILABLE_STATE = array('Contact initial' => 1, 'Devis envoye' => 1, 'En realisation' => 2, 'En attente de cloture' => 2, 'Stand-By' => 3, 'Cloturee' => 4, 'Avortee' => 5);

    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }


    /**
     * @return array  an array  2 fields :
     *  - file format, the expected file format
     *  - columns_format, expected columns in file
     */
    public function expectedFormat()
    {
        return array('file_format' => 'csv', 'columns_format' =>self::EXPECTED_FORMAT);
    }

    /**
     * @param UploadedFile $file resources file contzaining data to import.
     * @return mixed Process Import.
     * Process Import.
     */
    public function run(UploadedFile $file)
    {
        if ($file->guessExtension() == "txt") {//csv is seen as text/plain

            $i = 1;
            $inserted_projects = 0;
            $inserted_prospects = 0;
            if (($handle = fopen($file->getPathname(), "r")) !== FALSE) {

                $array_manager = array();//an array containing references to managers.
                $array_prospect = array();//an array containing references to projects.
                //iterate csv, row by row
                while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                    if ($i > 1 && $this->readArray($data, 'Intitule') != "") { //first row is column headers
                        $etude = $this->em->getRepository('mgateSuiviBundle:Etude')->findOneByNom($this->readArray($data, 'Intitule'));

                        if ($etude === null) {

                            //create project if it doesn't exists in DB
                            $e = new Etude();
                            $inserted_projects ++;
                            $e->setMandat($this->readArray($data, 'Exercice comptable'));
                           // $e->setNum($this->readArray($data, 'No Etude')); //untrusted, can be duplicated in siaje.
                            $e->setNom($this->readArray($data, 'Intitule'));
                            $e->setDescription($this->readArray($data, 'Domaine de compétence'));
                            $e->setDateCreation($this->dateManager($this->readArray($data, 'Date d\'ajout')));
                            if(array_key_exists($this->normalize($this->readArray($data, 'Statut')),self::SIAJE_AVAILABLE_STATE) ) {
                                $e->setStateID(self::SIAJE_AVAILABLE_STATE[$this->normalize($this->readArray($data, 'Statut'))]);
                            }
                            else{
                                $e->setStateID(self::SIAJE_AVAILABLE_STATE['Contact initial']);
                            }
                            $e->setAcompte(true);
                            if($this->readArray($data,'Acompte') !== null) {
                                $rate = explode(',', $this->readArray($data, 'Acompte'));//acompte is a percentage such as "30,00%".
                                $e->setPourcentageAcompte($rate['0'] / 100);
                            }
                            $e->setFraisDossier($this->readArray($data, 'Frais de dossier HT'));
                            $e->setPresentationProjet('Etude importée depuis Siaje');
                            $e->setDescriptionPrestation($this->readArray($data, 'Domaine de compétence'));
                                $e->setPourcentageAcompte($this->readArray($data,'Acompte'));
                            $this->em->persist($e);

                            /** Prospect management */
                            // Check if a prospect with same already exists in database
                            if($this->readArray($data, 'Entreprise', true) !== "") {
                                $prospect = $this->em->getRepository('mgatePersonneBundle:Prospect')->findOneByNom($this->readArray($data, 'Entreprise', true));
                                if($prospect === null) {//check if prospect already exist in local objects
                                    if (array_key_exists($this->readArray($data, 'Entreprise', true), $array_prospect)) {
                                        $prospect = $array_prospect[$this->readArray($data, 'Entreprise', true)];
                                    }
                                }
                            }
                            else {
                                $prospect = null;
                            }


                            if ($prospect !== null) {
                                $e->setProspect($prospect);
                            } else {
                                $p = new Prospect();
                                $inserted_prospects ++;
                                if($this->readArray($data, 'Entreprise', true) !== "") {
                                    $p->setNom($this->readArray($data, 'Entreprise', true));
                                }else{
                                    $p->setNom('Prospect sans nom '.rand());
                                }
                                $p->setAdresse($this->readArray($data, 'Adresse'));
                                $p->setCodePostal($this->readArray($data, 'Code Postal'));
                                $p->setVille($this->readArray($data, 'Ville'));


                                $contact = explode(' ', $this->normalize($this->readArray($data, 'Contact', true)));
                                $pe = new Personne();
                                $pe->setPrenom($contact[0]);//whitespace explode : not perfect but better than nothing
                                unset($contact[0]);
                                if(implode(' ', $contact) == ""){
                                    $pe->setNom('inconnu');
                                }
                                else{
                                    $pe->setNom(implode(' ', $contact));
                                }
                                $pe->setEmailEstValide(true);
                                $pe->setEstAbonneNewsletter(false);
                                $pe->setEmail($this->readArray($data, 'Email'));
                                $pe->setAdresse($this->readArray($data, 'Adresse'));
                                $pe->setCodePostal($this->readArray($data, 'Code Postal'));
                                $pe->setVille($this->readArray($data, 'Ville'));

                                $emp = new Employe();
                                $emp->setProspect($p);
                                $p->addEmploye($emp);
                                $emp->setPersonne($pe);
                                $this->em->persist($emp->getPersonne());
                                $this->em->persist($emp);
                                $this->em->persist($p);
                                $e->setProspect($p);
                                $array_prospect[$this->readArray($data, 'Entreprise', true)]= $p;


                            }

                            //create phases
                            $g = new GroupePhases();//default group
                            $g->setTitre('Imported from Siaje');
                            $g->setNumero(1);
                            $g->setDescription('Automatic description');
                            $g->setEtude($e);
                            $this->em->persist($g);

                            $ph = new Phase();
                            $ph->setEtude($e);
                            $ph->setGroupe($g);
                            $ph->setPosition(0);
                            $ph->setNbrJEH($this->readArray($data, 'JEHs'));
                            if($this->readArray($data, 'JEHs') > 0) {
                                $ph->setPrixJEH(round( $this->floatManager($this->readArray($data, 'Montant HT')) / $this->floatManager($this->readArray($data, 'JEHs')) ));
                            }
                            $ph->setTitre('Default phase');
                            $ph->setDelai($this->readArray($data, 'Durée en semaine') * 7);
                            $ph->setDateDebut($this->dateManager($this->readArray($data, 'Date signature CC')));
                            $this->em->persist($ph);

                            //manage project manager
                            $contact = explode(' ', $this->normalize($this->readArray($data, 'Suiveur principal', true)));
                            $firstname = $contact[0];
                            unset($contact[0]);
                            $surname = implode(' ', $contact);
                            $pm = $this->em->getRepository('mgatePersonneBundle:Personne')->findOneBy(array('nom' => $surname, 'prenom' => $firstname));

                            if ($pm !== null) {
                                $e->setSuiveur($pm);
                            } else {//create a new member and a new person
                                if(array_key_exists($this->readArray($data, 'Suiveur principal', true),$array_manager) && $this->readArray($data, 'Suiveur principal', true) != ''){//has already been created before
                                    $e->setSuiveur($array_manager[$this->readArray($data, 'Suiveur principal', true)]);
                                }
                                else {
                                    $pm = new Personne();
                                    $pm->setPrenom($firstname);
                                    if($surname == ""){
                                        $pm->setNom('inconnu');
                                    }
                                    else{
                                        $pm->setNom($surname);
                                    }
                                    $pm->setEmailEstValide(false);
                                    $pm->setEstAbonneNewsletter(false);
                                    $this->em->persist($pm);
                                    $m = new Membre();
                                    $m->setPersonne($pm);
                                    $this->em->persist($m);
                                    $e->setSuiveur($pm);
                                    $array_manager[$this->readArray($data, 'Suiveur principal', true)] = $pm;

                                }
                            }


                            //manage AP & CC
                            if($this->dateManager($this->readArray($data, 'Date signature CC')) !== null) {
                                $ap = new Ap();
                                $ap->setEtude($e);
                                $this->em->persist($ap);

                                $cc = new Cc();
                                $cc->setEtude($e);
                                $cc->setDateSignature($this->dateManager($this->readArray($data, 'Date signature CC')));
                                if (isset($pe)) {//if firm has been created in this loop iteration
                                    $cc->setSignataire2($pe);
                                }
                                $this->em->persist($cc);
                            }


                            //manage PVR
                            if($this->dateManager($this->readArray($data, 'Date signature PV')) !== null) {
                                $pv = new ProcesVerbal();
                                $pv->setEtude($e);
                                $pv->setDateSignature($this->dateManager($this->readArray($data, 'Date signature PV')));
                                $this->em->persist($pv);
                            }

                        }
                    }
                    $i++;
//                    if ($i % 25 == 0 or true) {
//                        $this->em->flush();
//                        $this->em->clear();
//                    }

                }
                fclose($handle);

                $this->em->flush();
            }


            return array('inserted_projects' => $inserted_projects, 'inserted_prospects' => $inserted_prospects);

        }
        return array('inserted_projects' => 0, 'inserted_prospects' => 0);
    }


}