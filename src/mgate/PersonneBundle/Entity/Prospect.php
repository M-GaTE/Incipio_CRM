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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * mgate\PersonneBundle\Entity\Prospect.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="mgate\PersonneBundle\Entity\ProspectRepository")
 */
class Prospect
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Employe", mappedBy="prospect")
     */
    private $employes;

    /**
     * , cascade={"persist"}.
     *
     * @ORM\OneToOne(targetEntity="\mgate\CommentBundle\Entity\Thread", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $thread;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=63)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="entite", type="integer", nullable=true)
     * @Assert\Choice(callback = "getEntiteChoiceAssert")
     */
    private $entite;

    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=127, nullable=true)
     */
    private $adresse;

	/**
     * @var int(5) $codepostal
     *
     * @ORM\Column(name="codepostal", type="integer", nullable=true)
     */
    private $codepostal;
	
	/**
     * @var string $ville
     *
     * @ORM\Column(name="ville", type="string", length=63, nullable=true)
     */
    private $ville;
	
	/**
     * @var string $pays
     *
     * @ORM\Column(name="pays", type="string", length=63, nullable=true)
     */
    private $pays;
	
	
	
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
     * Set thread.
     *
     * @param \mgate\CommentBundle\Entity\Thread $thread
     *
     * @return Prospect
     */
    public function setThread(\mgate\CommentBundle\Entity\Thread $thread)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * Get thread.
     *
     * @return mgate\CommentBundle\Entity\Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * Add employes.
     *
     * @param mgate\PersonneBundle\Entity\Employe $employes
     *
     * @return Prospect
     */
    public function addEmploye(\mgate\PersonneBundle\Entity\Employe $employes)
    {
        $this->employes[] = $employes;

        return $this;
    }

    /**
     * Remove employes.
     *
     * @param mgate\PersonneBundle\Entity\Employe $employes
     */
    public function removeEmploye(\mgate\PersonneBundle\Entity\Employe $employes)
    {
        $this->employes->removeElement($employes);
    }

    /**
     * Get employes.
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getEmployes()
    {
        return $this->employes;
    }

    /**
     * Set nom.
     *
     * @param string $nom
     *
     * @return Prospect
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom.
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->employes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set entite.
     *
     * @param string $entite
     *
     * @return Prospect
     */
    public function setEntite($entite)
    {
        $this->entite = $entite;

        return $this;
    }

    /**
     * Get entite.
     *
     * @return string
     */
    public function getEntite()
    {
        return $this->entite;
    }

    public static function getEntiteChoice()
    {
        return array(
            1 => 'Particulier',
            2 => 'Association',
            3 => 'TPE (moins de 20 salariés)',
            4 => 'PME / ETI (plus de 20 salariés)',
            5 => 'Grand Groupe',
            6 => 'Ecole',
            7 => 'Administration',
            8 => 'Junior-Entreprise',
            );
    }
    public static function getEntiteChoiceAssert()
    {
        return array_keys(self::getEntiteChoice());
    }

    public function getEntiteToString()
    {
        if (!$this->entite) {
            return '';
        }
        $tab = $this->getEntiteChoice();

        return $tab[$this->entite];
    }

    /**
     * Set adresse.
     *
     * @param string $adresse
     *
     * @return Prospect
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse.
     *
     * @return string
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

	/**
     * Set codepostal
     *
     * @param integer $codepostal
     * @return Prospect
     */
    public function setCodePostal($codepostal)
    {
        $this->codepostal = $codepostal;
    
        return $this;
    }

    /**
     * Get codePostal
     *
     * @return integer 
     */
    public function getCodePostal()
    {
        return $this->codepostal;
    }
	
	/**
     * Set ville
     *
     * @param string $ville
     * @return Prospect
     */
    public function setVille($ville)
    {
        $this->ville = $ville;
    
        return $this;
    }

    /**
     * Get ville
     *
     * @return string 
     */
    public function getVille()
    {
        return $this->ville;
    }
	
	/**
     * Set codepostal
     *
     * @param string $pays
     * @return Prospect
     */
    public function setPays($pays)
    {
        $this->pays = $pays;
    
        return $this;
    }

    /**
     * Get pays
     *
     * @return string 
     */
    public function getPays()
    {
        return $this->pays;
    }


    public function __toString()
    {
        return $this->getNom();
    }
}