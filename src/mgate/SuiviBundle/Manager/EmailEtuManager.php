<?php

namespace mgate\SuiviBundle\Manager;

use mgate\PersonneBundle\Entity\Membre;

class EmailEtuManager
{
    private $emailEtu;
    private $emailAncien;

    public function __construct($junior)
    {
        $this->emailEtu = array_key_exists('domaineEmailEtu', $junior) ? '@'.$junior['domaineEmailEtu'] : '@';
        $this->emailAncien = array_key_exists('domaineEmailAncien', $junior) ? '@'.$junior['domaineEmailAncien'] : '@';
    }

    /**
     * Get Adresse Mail Etu.
     *
     * @return string format@etu.emse.fr
     */
    public function getEmailEtu(Membre $membre)
    {
        $now = new \DateTime('now');
        $now = (int) $now->format('Y');

        if ($promo = $membre->getPromotion() && $membre->getPersonne()) {
            if ($promo < $now) {
                return preg_replace('#[^a-zA-Z.0-9_]#', '', $this->enMinusculeSansAccent($membre->getPersonne()->getPrenom().'.'.$membre->getPersonne()->getNom())).$this->emailAncien;
            } else {
                return preg_replace('#[^a-zA-Z.0-9_]#', '', $this->enMinusculeSansAccent($membre->getPersonne()->getPrenom().'.'.$membre->getPersonne()->getNom())).$this->emailEtu;
            }
        } elseif ($membre->getPersonne()) {
            return preg_replace('#[^a-zA-Z.0-9_]#', '', $this->enMinusculeSansAccent($membre->getPersonne()->getPrenom().'.'.$membre->getPersonne()->getNom())).$this->emailEtu;
        } else {
            return '';
        }
    }

    private function enMinusculeSansAccent($texte)
    {
        $texte = mb_strtolower($texte, 'UTF-8');
        $texte = str_replace(
            array(
                'à', 'â', 'ä', 'á', 'ã', 'å',
                'î', 'ï', 'ì', 'í',
                'ô', 'ö', 'ò', 'ó', 'õ', 'ø',
                'ù', 'û', 'ü', 'ú',
                'é', 'è', 'ê', 'ë',
                'ç', 'ÿ', 'ñ',
            ),
            array(
                'a', 'a', 'a', 'a', 'a', 'a',
                'i', 'i', 'i', 'i',
                'o', 'o', 'o', 'o', 'o', 'o',
                'u', 'u', 'u', 'u',
                'e', 'e', 'e', 'e',
                'c', 'y', 'n',
            ),
            $texte
        );

        return $texte;
    }
}
