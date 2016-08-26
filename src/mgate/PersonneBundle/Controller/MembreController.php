<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\PersonneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use mgate\PersonneBundle\Entity\Membre;
use mgate\PersonneBundle\Entity\Personne;
use mgate\PersonneBundle\Entity\Mandat;
use mgate\PersonneBundle\Form\MembreType;
use mgate\PubliBundle\Entity\RelatedDocument;

class MembreController extends Controller
{
    /**
     * @Secure(roles="ROLE_SUIVEUR")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('mgatePersonneBundle:Membre')->getMembres();

        return $this->render('mgatePersonneBundle:Membre:index.html.twig', array(
                    'membres' => $entities,
                ));
    }

    /**
     * @Secure(roles="ROLE_SUIVEUR")
     */
    public function listIntervenantsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $intervenants = $em->getRepository('mgatePersonneBundle:Membre')->getByMissionsNonNul();

        return $this->render('mgatePersonneBundle:Membre:indexIntervenants.html.twig', array(
                    'intervenants' => $intervenants,
                ));
    }

    /**
     * @Secure(roles="ROLE_SUIVEUR")
     */
    public function statistiqueAction($page)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('mgatePersonneBundle:Membre')->findAll();

        $membresActifs = array();
        foreach ($entities as $membre) {
            foreach ($membre->getMandats() as $mandat) {
                if ($mandat->getPoste()->getIntitule() == 'Membre' && $mandat->getDebutMandat() < new \DateTime('now') && $mandat->getFinMandat() > new \DateTime('now')) {
                    $membresActifs[] = $membre;
                }
            }
        }

        return $this->render('mgatePersonneBundle:Membre:index.html.twig', array(
                    'membres' => $membresActifs,
                ));
    }

    /**
     * @Secure(roles="ROLE_ELEVE")
     */
    public function voirAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('mgatePersonneBundle:Membre')->getMembreCompetences($id);

        if (!$entity) {
            throw $this->createNotFoundException('Le membre demandé n\'existe pas !');
        }

        return $this->render('mgatePersonneBundle:Membre:voir.html.twig', array(
                    'membre' => $entity,
        ));
    }

    /*
     * Ajout ET modification des membres (create if membre not existe )
     */

    /**
     * @Secure(roles="ROLE_SUIVEUR")
     */
    public function modifierAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $documentManager = $this->get('mgate.document_manager');

        if (!$membre = $em->getRepository('mgate\PersonneBundle\Entity\Membre')->find($id)) {
            $membre = new Membre();

            $now = new \DateTime('now');
            $now->modify('+ 3 year');

            $membre->setPromotion($now->format('Y'));

            $now = new \DateTime('now');
            $now->modify('- 20 year');
            $membre->setDateDeNaissance($now);
        }

        // Mail étudiant
        if (!$membre->getEmailEMSE()) {
            $email_etu_service = $this->container->get('mgate.email_etu');
            $membre->setEmailEMSE($email_etu_service->getEmailEtu($membre));
        }

        $form = $this->createForm(new MembreType(), $membre);
        $deleteForm = $this->createDeleteForm($id);

        $mandatsToRemove = $membre->getMandats()->toArray();

        $form = $this->createForm(new MembreType(), $membre);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->handleRequest($this->get('request'));
            $photoUpload = $form->get('photo')->getData();

            if ($form->isValid()) {

                // TODO TOREMOVE Specifique EMSE
                if ($membre->getPersonne()) {
                    // Photo de l'étudiant
                    $path = $membre->getPromotion().'/'.
                        preg_replace(
                            '#[^a-zA-Z0-9ÁÀÂÄÉÈÊËÍÌÎÏÓÒÔÖÚÙÛÜáàâäéèêëíìîïóòôöúùûüÇç\-_]#',
                            '_',
                        mb_strtolower($membre->getPersonne()->getNom(), 'UTF-8')
                    ).'_'.
                        preg_replace(
                            '#[^a-zA-Z0-9ÁÀÂÄÉÈÊËÍÌÎÏÓÒÔÖÚÙÛÜáàâäéèêëíìîïóòôöúùûüÇç\-_]#',
                            '_',
                        mb_strtolower($membre->getPersonne()->getPrenom(), 'UTF-8')
                    );
                } else {
                    $path = '';
                }
                $promo = $membre->getPromotion();

                /*
                 * Traitement de l'image de profil
                 */
                if ($membre->getPersonne()) {
                    $authorizedMIMEType = array('image/jpeg', 'image/png', 'image/bmp');
                    $photoInformation = new RelatedDocument();
                    $photoInformation->setMembre($membre);
                    $name = 'Photo - '.$membre->getIdentifiant().' - '.$membre->getPersonne()->getPrenomNom();

                    if ($photoUpload) {
                        $document = $documentManager->uploadDocumentFromFile($photoUpload, $authorizedMIMEType, $name, $photoInformation, true);
                        $membre->setPhotoURI($document->getWebPath());
                    } elseif (!$membre->getPhotoURI() && $promo !== null && $membre->getPersonne()) { // Spécifique EMSE
                        $ressourceURL = 'http://ismin.emse.fr/ismin/Photos/P'.urlencode($path);
                        $headers = get_headers($ressourceURL);
                        if (preg_match('#200#', $headers[0])) {
                            $document = $documentManager->uploadDocumentFromUrl($ressourceURL, $authorizedMIMEType, $name, $photoInformation, true);
                            $membre->setPhotoURI($document->getWebPath());
                        }
                    }
                }

                /*
                 * Traitement des postes
                 */
                if ($this->get('request')->get('add')) {
                    $mandatNew = new Mandat();
                    $poste = $em->getRepository('mgate\PersonneBundle\Entity\Poste')->findOneBy(array('intitule' => 'Membre'));
                    $dt = new \DateTime('now');
                    $dtl = clone $dt;
                    $dtl->modify('+1 year');

                    if ($poste) {
                        $mandatNew->setPoste($poste);
                    }
                    $mandatNew->setMembre($membre);
                    $mandatNew->setDebutMandat($dt);
                    $mandatNew->setFinMandat($dtl);
                    $membre->addMandat($mandatNew);
                }

                if (!$membre->getIdentifiant()) {
                    $initial = substr($membre->getPersonne()->getPrenom(), 0, 1).substr($membre->getPersonne()->getNom(), 0, 1);
                    $ident = count($em->getRepository('mgate\PersonneBundle\Entity\Membre')->findBy(array('identifiant' => $initial))) + 1;
                    while ($em->getRepository('mgate\PersonneBundle\Entity\Membre')->findOneBy(array('identifiant' => $initial.$ident))) {
                        ++$ident;
                    }
                    $membre->setIdentifiant(strtoupper($initial.$ident));
                }

                if (isset($now)) { // Si c'est un nouveau membre et qu'on ajoute un poste
                    $em->persist($membre);
                    $em->flush();

                    return $this->redirect($this->generateUrl('mgatePersonne_membre_modifier', array('id' => $membre->getId())));
                }

                // Suppression des mandat à supprimer
                    //Recherche des mandats supprimés
                foreach ($membre->getMandats() as $mandat) {
                    $key = array_search($mandat, $mandatsToRemove);
                    if ($key !== false) {
                        array_splice($mandatsToRemove, $key, 1);
                    }
                }
                    //Supression de la BDD
                foreach ($mandatsToRemove as $mandat) {
                    $em->remove($mandat);
                }

                $em->persist($membre); // persist $etude / $form->getData()
                $em->flush();

                $form = $this->createForm(new MembreType(), $membre);
            }
        }
        // TODO A modifier, l'ajout de poste dois se faire en js cf formation membre
        //if ($this->get('request')->get('save'))
         //   return $this->redirect($this->generateUrl('mgatePersonne_membre_voir', array('id' => $membre->getId())));

        return $this->render('mgatePersonneBundle:Membre:modifier.html.twig', array(
                    'form' => $form->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'photoURI' => $membre->getPhotoURI(),
                ));
    }

    /**
     * @Secure(roles="ROLE_SUIVEUR")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (!$entity = $em->getRepository('mgate\PersonneBundle\Entity\Membre')->find($id)) {
                throw $this->createNotFoundException('Le membre demandé n\'existe pas !');
            }

            if ($entity->getPersonne()) {
                $entity->getPersonne()->setMembre(null);
                if ($entity->getPersonne()->getUser()) {
                    // pour pouvoir réattribuer le compte
                    $entity->getPersonne()->getUser()->setPersonne(null);
                }
                $entity->getPersonne()->setUser(null);
            }
            $entity->setPersonne(null);
            //est-ce qu'on supprime la personne aussi ?

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('mgatePersonne_membre_homepage'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

    /**
     * @Secure(roles="ROLE_SUIVEUR")
     */
    public function impayesAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('mgatePersonneBundle:Membre')->findByformatPaiement('aucun');

        return $this->render('mgatePersonneBundle:Membre:impayes.html.twig', array(
            'membres' => $entities,
        ));
    }



}
