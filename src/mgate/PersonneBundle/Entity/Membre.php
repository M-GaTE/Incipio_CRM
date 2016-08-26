<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\PersonneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use mgate\PersonneBundle\Entity\Mandat;
use mgate\PersonneBundle\Entity\Personne;
use mgate\PersonneBundle\Entity\Poste;
use mgate\PubliBundle\Entity\RelatedDocument;
use mgate\SuiviBundle\Entity\Mission;
use n7consulting\RhBundle\Entity\Competence;

/**
 * mgate\PersonneBundle\Entity\Membre.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="mgate\PersonneBundle\Entity\MembreRepository")
 */
class Membre
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="mgate\SuiviBundle\Entity\Mission", mappedBy="intervenant", cascade={"persist","remove"})
     */
    private $missions;

    /**
     * @ORM\OneToOne(targetEntity="mgate\PersonneBundle\Entity\Personne", inversedBy="membre", fetch="EAGER", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $personne;

    /**
     * @var \Date
     *
     * @ORM\Column(name="dateCE", type="date",nullable=true)
     */
    private $dateConventionEleve;

    /**
     * @var string
     *
     * @ORM\Column(name="identifiant", type="string", length=10, nullable=true, unique=true)
     */
    private $identifiant;

    /**
     * @var string
     *
     * @ORM\Column(name="emailEMSE", type="string", length=50, nullable=true)
     */
    private $emailEMSE;

    /**
     * @var int
     * @ORM\Column(name="promotion", type="smallint", nullable=true)
     */
    private $promotion;

    /**
     * @var date
     * @ORM\Column(name="birthdate", type="date", nullable=true)
     */
    private $dateDeNaissance;

    /**
     * @var string
     * @ORM\Column(name="placeofbirth", type="string", nullable=true)
     */
    private $lieuDeNaissance;

    /**
     * @ORM\OneToMany(targetEntity="mgate\PersonneBundle\Entity\Mandat", mappedBy="membre", cascade={"persist","remove"})
     */
    private $mandats;

    /**
     * @var string
     * @ORM\Column(name="nationalite", type="string", nullable=true)
     */
    private $nationalite;

    /**
     * @ORM\OneToMany(targetEntity="mgate\PubliBundle\Entity\RelatedDocument", mappedBy="membre", cascade={"remove"})
     */
    private $relatedDocuments;

    /**
     * @var string
     * @ORM\Column(name="photoURI", type="string", nullable=true)
     */
    private $photoURI;
	
	/** Ajout N7 Consulting **/
	
    /**
     * @var string
     * @ORM\Column(name="formatPaiement", type="string", length=15)
     */
    private $formatPaiement;
	
	/**
     * @var bool
     * @ORM\Column(name="estSocieteGenerale", type="boolean", nullable=false, options={"default" = false})
     */
    private $estSocieteGenerale;
	
	/**
     * @var string
     * @ORM\Column(name="filiere", type="string", length=15)
     */
    private $filiere;

    /**
     * @ORM\ManyToMany(targetEntity="n7consulting\RhBundle\Entity\Competence", mappedBy="membres", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $competences;


    public function __construct()
    {
        $this->mandats = new ArrayCollection();
        $this->missions = new ArrayCollection();
        $this->relatedDocuments = new ArrayCollection();
        $this->competences = new ArrayCollection();

    }

    public function __toString()
    {
        return $this->getPersonne()->__toString();
    }


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set identifiant.
     *
     * @param string $identifiant
     *
     * @return Membre
     */
    public function setIdentifiant($identifiant)
    {
        $this->identifiant = $identifiant;

        return $this;
    }

    /**
     * Get identifiant.
     *
     * @return string
     */
    public function getIdentifiant()
    {
        return $this->identifiant;
    }

    /**
     * Set personne.
     *
     * @param Personne $personne
     *
     * @return Membre
     */
    public function setPersonne(Personne $personne = null)
    {
        if ($personne !== null) {
            $personne->setMembre($this);
        }
        $this->personne = $personne;

        return $this;
    }

    /**
     * Get personne.
     *
     * @return Personne
     */
    public function getPersonne()
    {
        return $this->personne;
    }

    /**
     * Set poste.
     *
     * @param \mgate\PersonneBundle\Entity\Poste $poste
     *
     * @return Membre
     */
    public function setPoste(Poste $poste = null)
    {
        $this->poste = $poste;

        return $this;
    }

    /**
     * Get poste.
     *
     * @return \mgate\PersonneBundle\Entity\Membre
     */
    public function getPoste()
    {
        return $this->poste;
    }

    /**
     * Add mandats.
     *
     * @param Mandat $mandats
     *
     * @return Membre
     */
    public function addMandat(Mandat $mandats)
    {
        $this->mandats[] = $mandats;

        return $this;
    }

    /**
     * Remove mandats.
     *
     * @param Mandat $mandats
     */
    public function removeMandat(Mandat $mandats)
    {
        $this->mandats->removeElement($mandats);
    }

    /**
     * Get mandats.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMandats()
    {
        return $this->mandats;
    }

    /**
     * Set promotion.
     *
     * @param int $promotion
     *
     * @return Membre
     */
    public function setPromotion($promotion)
    {
        $this->promotion = $promotion;

        return $this;
    }

    /**
     * Get promotion.
     *
     * @return int
     */
    public function getPromotion()
    {
        return $this->promotion;
    }

    /**
     * Set dateDeNaissance.
     *
     * @param \DateTime $dateDeNaissance
     *
     * @return Membre
     */
    public function setDateDeNaissance($dateDeNaissance)
    {
        $this->dateDeNaissance = $dateDeNaissance;

        return $this;
    }

    /**
     * Get dateDeNaissance.
     *
     * @return \DateTime
     */
    public function getDateDeNaissance()
    {
        return $this->dateDeNaissance;
    }

    /**
     * Set lieuDeNaissance.
     *
     * @param string $lieuDeNaissance
     *
     * @return Membre
     */
    public function setLieuDeNaissance($lieuDeNaissance)
    {
        $this->lieuDeNaissance = $lieuDeNaissance;

        return $this;
    }

    /**
     * Get lieuDeNaissance.
     *
     * @return string
     */
    public function getLieuDeNaissance()
    {
        return $this->lieuDeNaissance;
    }

    /**
     * Set nationalite.
     *
     * @param string $nationalite
     *
     * @return Membre
     */
    public function setNationalite($nationalite)
    {
        $this->nationalite = $nationalite;

        return $this;
    }

    /**
     * Get nationalite.
     *
     * @return string
     */
    public function getNationalite()
    {
        return $this->nationalite;
    }

    /**
     * Set emailEMSE.
     *
     * @param string $emailEMSE
     *
     * @return Membre
     */
    public function setEmailEMSE($emailEMSE)
    {
        $this->emailEMSE = $emailEMSE;

        return $this;
    }

    /**
     * Get emailEMSE.
     *
     * @return string
     */
    public function getEmailEMSE()
    {
        return $this->emailEMSE;
    }

    /**
     * Set dateConventionEleve.
     *
     * @param \DateTime $dateConventionEleve
     *
     * @return Membre
     */
    public function setDateConventionEleve($dateConventionEleve)
    {
        $this->dateConventionEleve = $dateConventionEleve;

        return $this;
    }

    /**
     * Get dateConventionEleve.
     *
     * @return \DateTime
     */
    public function getDateConventionEleve()
    {
        return $this->dateConventionEleve;
    }

    /**
     * Add missions.
     *
     * @param Mission $missions
     *
     * @return Membre
     */
    public function addMission(Mission $missions)
    {
        $this->missions[] = $missions;

        return $this;
    }

    /**
     * Remove missions.
     *
     * @param Mission $missions
     */
    public function removeMission(Mission $missions)
    {
        $this->missions->removeElement($missions);
    }

    /**
     * Get missions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMissions()
    {
        return $this->missions;
    }

    /**
     * Add relatedDocuments.
     *
     * @param RelatedDocument $relatedDocuments
     *
     * @return Membre
     */
    public function addRelatedDocument(RelatedDocument $relatedDocuments)
    {
        $this->relatedDocuments[] = $relatedDocuments;

        return $this;
    }

    /**
     * Remove relatedDocuments.
     *
     * @param RelatedDocument $relatedDocuments
     */
    public function removeRelatedDocument(RelatedDocument $relatedDocuments)
    {
        $this->relatedDocuments->removeElement($relatedDocuments);
    }

    /**
     * Get relatedDocuments.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRelatedDocuments()
    {
        return $this->relatedDocuments;
    }

    /**
     * Set photoURI.
     *
     * @param string $photoURI
     *
     * @return Membre
     */
    public function setPhotoURI($photoURI)
    {
        $this->photoURI = $photoURI;

        return $this;
    }

    /**
     * Get photoURI.
     *
     * @return string
     */
    public function getPhotoURI()
    {
        return $this->photoURI;
    }
	
	/**
     * Set formatPaiement.
     * @param string $formatPaiement
     * @return Membre
     */
    public function setformatPaiement($formatPaiement)
    {
        $this->formatPaiement = $formatPaiement;

        return $this;
    }

    /**
     * Get formatPaiement.
     *
     * @return string
     */
    public function getformatPaiement()
    {
        return $this->formatPaiement;
    }    
	
	/**
     * Set estSocieteGenerale.
     *
     * @param string $estSocieteGenerale
     *
     * @return Membre
     */
    public function setestSocieteGenerale($estSocieteGenerale)
    {
        $this->estSocieteGenerale = $estSocieteGenerale;

        return $this;
    }

    /**
     * Get estSocieteGenerale.
     *
     * @return string
     */
    public function getestSocieteGenerale()
    {
        return $this->estSocieteGenerale;
    }
	
		/**
     * Set filiere.
     * @param string $filiere
     * @return Membre
     */
    public function setFiliere($filiere)
    {
        $this->filiere = $filiere;

        return $this;
    }

    /**
     * Get filiere.
     *
     * @return string
     */
    public function getFiliere()
    {
        return $this->filiere;
    }

    /**
     * Add missions.
     *
     * @param Competence $competences
     *
     * @return Membre
     */
    public function addCompetence(Competence $competences)
    {
        $this->competences[] = $competences;

        return $this;
    }

    /**
     * Remove missions.
     *
     * @param Competence $competences
     */
    public function removeCompetence(Competence $competences)
    {
        $this->competences->removeElement($competences);
    }

    /**
     * Get competences.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCompetences()
    {
        return $this->competences;
    }

	
}
