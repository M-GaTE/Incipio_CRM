<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\PubliBundle\Controller;

use Mgate\PubliBundle\Entity\Document;
use Mgate\PubliBundle\Form\Type\DocTypeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TraitementController extends Controller
{
    const DOCTYPE_SUIVI_ETUDE = 'FSE';
    const DOCTYPE_DEVIS = 'DEVIS';
    const DOCTYPE_AVANT_PROJET = 'AP';
    const DOCTYPE_CONVENTION_CLIENT = 'CC';
    const DOCTYPE_FACTURE_ACOMTE = 'FA';
    const DOCTYPE_FACTURE_INTERMEDIAIRE = 'FI';
    const DOCTYPE_FACTURE_SOLDE = 'FS';
    const DOCTYPE_PROCES_VERBAL_INTERMEDIAIRE = 'PVI';
    const DOCTYPE_PROCES_VERBAL_FINAL = 'PVR';
    const DOCTYPE_RECAPITULATIF_MISSION = 'RM';
    const DOCTYPE_DESCRIPTIF_MISSION = 'DM';
    const DOCTYPE_CONVENTION_ETUDIANT = 'CE';
    const DOCTYPE_FICHE_ADHESION = 'FM';
    const DOCTYPE_ACCORD_CONFIDENTIALITE = 'AC';
    const DOCTYPE_DECLARATION_ETUDIANT_ETR = 'DEE';
    const DOCTYPE_NOTE_DE_FRAIS = 'NF';
    const DOCTYPE_BULLETIN_DE_VERSEMENT = 'BV';

    const ROOTNAME_ETUDE = 'etude';
    const ROOTNAME_PROCES_VERBAL = 'pvr';
    const ROOTNAME_ETUDIANT = 'etudiant';
    const ROOTNAME_MISSION = 'mission';
    const ROOTNAME_NOTE_DE_FRAIS = 'nf';
    const ROOTNAME_FACTURE = 'facture';
    const ROOTNAME_BULLETIN_DE_VERSEMENT = 'bv';

    // On considère que les TAG ont déjà été nettoyé du XML
    const REG_REPEAT_LINE = "#(<w:tr(?:(?!w:tr\s).)*?)(\{\%\s*TRfor[^\%]*\%\})(.*?)(\{\%\s*endforTR\s*\%\})(.*?</w:tr>)#";
    const REG_REPEAT_PARAGRAPH = "#(<w:p(?:(?!<w:p\s).)*?)(\{\%\s*Pfor[^\%]*\%\})(.*?)(\{\%\s*endforP\s*\%\})(.*?</w:p>)#";
    // Champs
    const REG_CHECK_FIELDS = "#\{[^\}%]*?[\{%][^\}%]+?[\}%][^\}%]*?\}#";
    const REG_XML_NODE_IDENTIFICATOR = '#<.*?>#';
    // Images
    const REG_IMAGE_DOC = '#<w:drawing.*?/w:drawing>#';
    const REG_IMAGE_DOC_FIELD = '#wp:extent cx="(\\d+)" cy="(\\d+)".*wp:docPr.*descr="(.*?)".*a:blip r:embed="(rId\\d+)#';
    const REG_IMAGE_REL = '#Id="(rId\\d+)" Type="\\S*" Target="media\\/(image\\d+.(jpeg|jpg|png))"#';
    const IMAGE_FIX = '#imageFIX#';
    const IMAGE_VAR = '#imageVAR#';
    // Autres
    const REG_SPECIAL_CHAR = '{}()[]|><?=;!+*-/';
    const REG_FILE_EXT = "#\.(jpg|png|jpeg)#i";

    private $idDocx;
    private $refDocx;

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function publiposterAction($templateName, $rootName, $rootObject_id)
    {
        $this->publipostage($templateName, $rootName, $rootObject_id);

        return $this->telechargerAction($templateName);
    }

    private function publipostage($templateName, $rootName, $rootObject_id, $debug = false)
    {
        $em = $this->getDoctrine()->getManager();

        $errorRootObjectNotFound = $this->createNotFoundException('Le document ne peut être publiposté car l\'objet de référence n\'existe pas !');
        $errorEtudeConfidentielle = new AccessDeniedException('Cette étude est confidentielle');

        switch ($rootName) {
            case self::ROOTNAME_ETUDE:
                if (!$rootObject = $em->getRepository('Mgate\SuiviBundle\Entity\Etude')->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                if ($this->get('Mgate.etude_manager')->confidentielRefus($rootObject, $this->getUser(), $this->get('security.authorization_checker'))) {
                    throw $errorEtudeConfidentielle;
                }
                break;
            case self::ROOTNAME_ETUDIANT:
                if (!$rootObject = $em->getRepository('Mgate\PersonneBundle\Entity\Membre')->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                break;
            case self::ROOTNAME_MISSION:
                if (!$rootObject = $em->getRepository('Mgate\SuiviBundle\Entity\Mission')->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                break;
            case self::ROOTNAME_FACTURE:
                if (!$rootObject = $em->getRepository('Mgate\TresoBundle\Entity\Facture')->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                if ($rootObject->getEtude() && $this->get('Mgate.etude_manager')->confidentielRefus($rootObject->getEtude(), $this->getUser(), $this->get('security.authorization_checker'))) {
                    throw $errorEtudeConfidentielle;
                }
                break;
            case self::ROOTNAME_NOTE_DE_FRAIS:
                if (!$rootObject = $em->getRepository('Mgate\TresoBundle\Entity\NoteDeFrais')->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                break;
            case self::ROOTNAME_BULLETIN_DE_VERSEMENT:
                if (!$rootObject = $em->getRepository('Mgate\TresoBundle\Entity\BV')->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                if ($rootObject->getMission() && $rootObject->getMission()->getEtude() && $this->get('Mgate.etude_manager')->confidentielRefus($rootObject->getMission()->getEtude(), $this->getUser(), $this->get('security.authorization_checker'))) {
                    throw $errorEtudeConfidentielle;
                }
                break;
            case self::ROOTNAME_PROCES_VERBAL:
                if (!$rootObject = $em->getRepository('Mgate\SuiviBundle\Entity\ProcesVerbal')->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                if ($rootObject->getEtude() && $this->get('Mgate.etude_manager')->confidentielRefus($rootObject->getEtude(), $this->getUser(), $this->get('security.authorization_checker'))) {
                    throw $errorEtudeConfidentielle;
                }
                break;
            default:
                throw $this->createNotFoundException('Publipostage invalide ! Pas de bol...');
                break;
        }

        $chemin = $this->getDoctypeAbsolutePathFromName($templateName, $debug);

        $templatesXMLtraite = $this->traiterTemplates($chemin, $rootName, $rootObject);
        $repertoire = 'tmp';

        //SI DM on prend la ref de RM et ont remplace RM par DM
        if ($templateName == self::DOCTYPE_DESCRIPTIF_MISSION) {
            $templateName = 'RM';
            $isDM = true;
        }

        if ($rootName == 'etude' && $rootObject->getReference()) {
            if (!$debug) {
                //avoid collision with references using / or other characters.
                $refDocx = $rootObject->getReference().'-'.$templateName.'-';
            } else {
                $refDocx = '';
            }
        } elseif ($rootName == 'etudiant') {
            $refDocx = $templateName.'-'.$rootObject->getIdentifiant();
        } else {
            $refDocx = 'UNREF';
        }

        //On remplace DM par RM si DM
        if (isset($isDM) && $isDM) {
            $refDocx = preg_replace('#RM#', 'DM', $refDocx);
        }

        $idDocx = $refDocx.'-'.((int) strtotime('now') + rand());
        copy($chemin, $repertoire.'/'.$idDocx);
        $zip = new \ZipArchive();
        $zip->open($repertoire.'/'.$idDocx);

        /*
         * TRAITEMENT INSERT IMAGE
         */
        $images = array();
        //Gantt
        if ($templateName == 'AP' || (isset($isDM) && $isDM)) {
            $chartManager = $this->get('Mgate.chart_manager');
            $ob = $chartManager->getGantt($rootObject, $templateName);
            if ($chartManager->exportGantt($ob, $idDocx)) {
                $image = array();
                $image['fileLocation'] = "$repertoire/$idDocx.png";
                $info = getimagesize("$repertoire/$idDocx.png");
                $image['width'] = $info[0];
                $image['height'] = $info[1];
                $images['imageVARganttAP'] = $image;
            }
        }

        //Intégration temporaire
        $imagesInDocx = $this->traiterImages($templatesXMLtraite, $images);
        foreach ($imagesInDocx as $image) {
            $zip->deleteName('word/media/'.$image[2]);
            $zip->addFile($repertoire.'/'.$idDocx.'.png', 'word/media/'.$image[2]);
        }
        /*****/

        $zip = new \ZipArchive();
        $zip->open($repertoire.'/'.$idDocx);

        foreach ($templatesXMLtraite as $templateXMLName => $templateXMLContent) {
            $zip->deleteName('word/'.$templateXMLName);
            $zip->addFromString('word/'.$templateXMLName, $templateXMLContent);
        }

        $zip->close();

        $this->idDocx = $idDocx;
        $this->refDocx = $refDocx;

        return true;
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     */
    public function telechargerAction($templateName)
    {
        $this->purge();
        if (isset($this->idDocx) && isset($this->refDocx)) {
            $idDocx = $this->idDocx;
            $refDocx = $this->refDocx;

            $templateName = 'tmp/'.$idDocx;

            $response = new Response();
            $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            $response->headers->set('Content-Length', filesize($templateName));
            $response->headers->set('Content-disposition', 'attachment; filename="'.$refDocx.'.docx"');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->headers->set('Expires', 0);

            $response->setContent(file_get_contents($templateName));

            return $response;
        }

        return $this->redirect($this->generateUrl('MgateSuivi_etude_homepage', array('page' => 1)));
    }

    private function arrayPushAssoc(&$array, $key, $value)
    {
        $array[$key] = $value;

        return $array;
    }

    private function getDoctypeAbsolutePathFromName($doc, $debug = false)
    {
        $em = $this->getDoctrine()->getManager();

        // Utilisé pour tester un template lors de l'upload d'un nouveau
        if ($debug) {
            return $doc;
        }

        if (!$documenttype = $em->getRepository('Mgate\PubliBundle\Entity\Document')->findOneBy(array('name' => $doc))) {
            throw $this->createNotFoundException('Le doctype '.$doc.' n\'existe pas... C\'est bien balo');
        } else {
            $chemin = $this->get('kernel')->getRootDir().''.$documenttype::DOCUMENT_STORAGE_ROOT.'/'.$documenttype->getPath();
        }

        return $chemin;
    }

    //Prendre tous les fichiers dans word
    private function getDocxContent($docxFullPath)
    {
        $zip = new \ZipArchive();
        $templateXML = array();
        if ($zip->open($docxFullPath) === true) {
            for ($i = 0; $i < $zip->numFiles; ++$i) {
                $name = $zip->getNameIndex($i);
                if ((strstr($name, 'document') || strstr($name, 'header') || strstr($name, 'footer')) && !strstr($name, 'rels')) {
                    $this->arrayPushAssoc($templateXML, str_replace('word/', '', $name), $zip->getFromIndex($i));
                }
            }
            $zip->close();
        }

        return $templateXML;
    }

    //prendre le fichier relationShip
    private function getDocxRelationShip($docxFullPath)
    {
        $zip = new \ZipArchive();
        $templateXML = array();
        if ($zip->open($docxFullPath) === true) {
            for ($i = 0; $i < $zip->numFiles; ++$i) {
                $name = $zip->getNameIndex($i);
                if ((strstr($name, 'document.xml.rel'))) {
                    $templateXML = $zip->getFromIndex($i);
                }
            }
            $zip->close();
        }

        return $templateXML;
    }

    private function traiterTemplates($templateFullPath, $rootName, $rootObject)
    {
        $templatesXML = $this->getDocxContent($templateFullPath); //récup contenu XML
        $templatesXMLTraite = array();

        foreach ($templatesXML as $templateName => $templateXML) {
            $templateXML = $this->get('twig')->render($templateXML, array($rootName => $rootObject));
            $this->arrayPushAssoc($templatesXMLTraite, $templateName, $templateXML);
        }

        return $templatesXMLTraite;
    }

    private function traiterImages(&$templatesXML, $images)
    {
        $allmatches = array();
        foreach ($templatesXML as $key => $templateXML) {
            $i = preg_match_all('#<!--IMAGE\|(.*?)\|\/IMAGE-->#', $templateXML, $matches);
            while ($i--) {
                $splited = preg_split("#\|#", $matches[1][$i]);
                if (isset($images[$splited[0]])) {
                    if (preg_match('#VAR#', $splited[0])) {
                        $cx = $splited[3];
                        $cy = $images[$splited[0]]['height'] * $cx / $images[$splited[0]]['width'];

                        $cx = round($cx);
                        $cy = round($cy);

                        $replacement = array();
                        preg_match("#wp:extent cx=\"$splited[3]\" cy=\"$splited[4]\".*wp:docPr.*a:blip r:embed=\"$splited[1]\".*a:ext cx=\"$splited[3]\" cy=\"$splited[4]\"#", $templateXML, $replacement);
                        $replacement = $replacement[0];
                        $replacement = preg_replace("#cy=\"$splited[4]\"#", "cy=\"$cy\"", $replacement);
                        $templatesXML[$key] = preg_replace("#wp:extent cx=\"$splited[3]\" cy=\"$splited[4]\".*wp:docPr.*a:blip r:embed=\"$splited[1]\".*a:ext cx=\"$splited[3]\" cy=\"$splited[4]\"#", $replacement, $templateXML);
                    }
                }
                array_push($allmatches, $splited);
            }
        }

        return $allmatches;
    }

    //Nettoie le dossier tmp : efface les fichiers temporaires vieux de plus de 1 jours
    private function purge()
    {
        $oldSec = 86400; // = 1 Jours
        clearstatcache();
        $glob = glob('tmp/*');
        if ($glob !== false) {
            foreach ($glob as $filename) {
                if (filemtime($filename) + $oldSec < time()) {
                    unlink($filename);
                }
            }
        }
    }

    /**
     * Traitement des champs (Nettoyage XML).
     */
    private function cleanDocxFields(&$templateXML)
    {
        $fields = array();
        preg_match_all(self::REG_CHECK_FIELDS, $templateXML, $fields);
        $fields = $fields[0];
        foreach ($fields as $field) {
            $originalField = $field;
            $field = preg_replace('#‘#', '\'', $field); // Peut etre simplifier en une ligne avec un array
            $field = preg_replace('#’#', '\'', $field);
            $field = preg_replace('#«#', '"', $field);
            $field = preg_replace('#»#', '"', $field);
            $field = preg_replace(self::REG_XML_NODE_IDENTIFICATOR, '', $field);
            if ($field == strtoupper($field)) {
                $field = strtolower($field);
            }
            $templateXML = preg_replace('#'.addcslashes(addslashes($originalField), self::REG_SPECIAL_CHAR).'#', html_entity_decode($field), $templateXML);
        }

        return $templateXML;
    }

    /**
     * Traitement des lignes de tableaux.
     */
    private function cleanDocxTableRow(&$templateXML)
    {
        $parts = array();
        $nbr = preg_match_all(self::REG_REPEAT_LINE, $templateXML, $parts);
        $datas = array();
        foreach ($parts as $part) {
            for ($i = 0; $i < $nbr; ++$i) {
                $datas[$i][] = $part[$i];
            }
        }

        foreach ($datas as $data) {
            $forStart = $data[2];
            $forEnd = $data[4];

            $body = preg_replace(array(
               '#'.addcslashes(addslashes($forStart), self::REG_SPECIAL_CHAR).'#',
               '#'.addcslashes(addslashes($forEnd), self::REG_SPECIAL_CHAR).'#', ), '', $data[0]);

            $templateXML = preg_replace('#'.addcslashes(addslashes($data[0]), self::REG_SPECIAL_CHAR).'#', preg_replace('#TRfor#', 'for', $forStart).$body.'{% endfor %}', $templateXML);
        }

        return $templateXML;
    }

    /**
     * Traitement Paragraphe.
     */
    private function cleanDocxParagraph(&$templateXML)
    {
        $parts = array();
        $nbr = preg_match_all(self::REG_REPEAT_PARAGRAPH, $templateXML, $parts);
        $datas = array();
        foreach ($parts as $part) {
            for ($i = 0; $i < $nbr; ++$i) {
                $datas[$i][] = $part[$i];
            }
        }

        foreach ($datas as $data) {
            $forStart = $data[2];
            $forEnd = $data[4];

            $body = preg_replace(array(
               '#'.addcslashes(addslashes($forStart), self::REG_SPECIAL_CHAR).'#',
               '#'.addcslashes(addslashes($forEnd), self::REG_SPECIAL_CHAR).'#', ), '', $data[0]);

            $templateXML = preg_replace('#'.addcslashes(addslashes($data[0]), self::REG_SPECIAL_CHAR).'#', preg_replace('#Pfor#', 'for', $forStart).$body.'{% endfor %}', $templateXML);
        }

        return $templateXML;
    }

    /**
     * Traitement des images.
     */
    private function linkDocxImages(&$templateXML, $relationship)
    {
        $images = array();
        preg_match(self::REG_IMAGE_DOC, $templateXML, $images);

        foreach ($images as $image) {
            $imageInfo = array();
            if (preg_match(self::REG_IMAGE_DOC_FIELD, $image, $imageInfo)) {
                $cx = $imageInfo[1];
                $cy = $imageInfo[2];
                $fileName = explode('\\', $imageInfo[3]);
                $originalFilename = preg_replace(self::REG_FILE_EXT,  '', end($fileName));
                $rId = $imageInfo[4];

                if (preg_match(self::IMAGE_VAR, $originalFilename) || preg_match(self::IMAGE_VAR, $originalFilename)) {
                    $relatedImage = array();
                    preg_match(self::REG_IMAGE_REL, $relationship, $relatedImage);
                    $localFilename = $relatedImage[2];

                    $commentsRel = '<!--IMAGE|'.$originalFilename.'|'.$rId.'|'.$localFilename.'|'.$cx.'|'.$cy.'|/IMAGE-->';
                    $templateXML = preg_replace("#(<\?.*?\?>)#", "$0$commentsRel", $templateXML, 1);
                }
            }
        }

        return $templateXML;
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function uploadNewDoctypeAction(Request $request)
    {
        $data = array();
        $form = $this->createForm(DocTypeType::class, $data);
        $session = $request->getSession();

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                // Création d'un fichier temporaire
                $file = $data['template'];
                $filename = sha1(uniqid(mt_rand(), true));
                $filename .= '.'.$file->guessExtension();
                $file->move('tmp/', $filename);
                $docxFullPath = 'tmp/'.$filename;

                // Extraction des infos XML
                $templatesXML = $this->getDocxContent($docxFullPath);
                $relationship = $this->getDocxRelationShip($docxFullPath);
                // Nettoyage des XML
                $templatesXMLTraite = array();
                foreach ($templatesXML as $templateName => $templateXML) {
                    $this->cleanDocxFields($templateXML);
                    $this->cleanDocxTableRow($templateXML);
                    $this->cleanDocxParagraph($templateXML);
                    $this->linkDocxImages($templateXML, $relationship);
                    $this->arrayPushAssoc($templatesXMLTraite, $templateName, $templateXML);
                }

                // Enregistrement dans le fichier temporaire
                $zip = new \ZipArchive();
                $zip->open($docxFullPath);

                foreach ($templatesXMLTraite as $templateXMLName => $templateXMLContent) {
                    $zip->deleteName('word/'.$templateXMLName);
                    $zip->addFromString('word/'.$templateXMLName, $templateXMLContent);
                }
                $zip->close();

                if (array_key_exists('etude', $data)) {
                    $etude = $data['etude'];
                } else {
                    $etude = null;
                }
                // Vérification du template (document étude)
                if ($etude && ($data['name'] == self::DOCTYPE_AVANT_PROJET ||
                    $data['name'] == self::DOCTYPE_CONVENTION_CLIENT ||
                    $data['name'] == self::DOCTYPE_SUIVI_ETUDE) &&
                    $data['verification'] && $this->publipostage($docxFullPath, self::ROOTNAME_ETUDE, $etude->getId(), true)
                    ) {
                    $session->getFlashBag()->add('success', 'Le template a été vérifié, il ne contient pas d\'erreur');
                }

                if (array_key_exists('etudiant', $data)) {
                    $etudiant = $data['etudiant'];
                } else {
                    $etudiant = null;
                }

                $etudiant = $data['etudiant'];
                // Vérification du template (document étudiant)
                if ($etudiant && ($data['name'] == self::DOCTYPE_CONVENTION_ETUDIANT ||
                    $data['name'] == self::DOCTYPE_DECLARATION_ETUDIANT_ETR) &&
                    $data['verification'] && $this->publipostage($docxFullPath, self::ROOTNAME_ETUDIANT, $etudiant->getId(), true)
                    ) {
                    $session->getFlashBag()->add('success', 'Le template a été vérifié, il ne contient pas d\'erreur');
                }

                // Enregistrement du template
                $em = $this->getDoctrine()->getManager();
                $user = $this->getUser();
                $personne = $user->getPersonne();
                $file = new File($docxFullPath);

                $doc = new Document();
                $doc->setAuthor($personne)
                    ->setName($data['name'])
                    ->setFile($file);
                $kernel = $this->get('kernel');
                $doc->setRootDir($kernel->getRootDir());
                $em->persist($doc);
                $docs = $em->getRepository('MgatePubliBundle:Document')->findBy(array('name' => $doc->getName()));
                foreach ($docs as $doc) {
                    $doc->setRootDir($kernel->getRootDir());
                    $em->remove($doc);
                }
                $em->flush();

                $session->getFlashBag()->add('success', 'Le document a été mis à jour');

                return $this->redirect($this->generateUrl('Mgate_publi_documenttype_upload'));
            }
        }

        return $this->render('MgatePubliBundle:DocType:upload.html.twig',
                            array('form' => $form->createView())
                );
    }
}
