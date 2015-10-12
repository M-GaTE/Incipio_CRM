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

/**
 * mgate\PersonneBundle\Entity\Personne.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="mgate\PersonneBundle\Entity\PersonneRepository")
 */
class Personne
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
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="sexe", type="string", length=15)
     */
    private $sexe;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=31, nullable=true)
     */
    private $mobile;

    /**
     * @var string
     *
     * @ORM\Column(name="fix", type="string", length=31, nullable=true)
     */
    private $fix;

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
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=63, nullable=true)
     */
    private $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="emailestvalide", type="boolean", nullable=false, options={"default" = true})
     */
    private $emailEstValide;

    /**
     * @var bool
     * @ORM\Column(name="estabonnenewsletter", type="boolean", nullable=false, options={"default" = true})
     */
    private $estAbonneNewsletter;

    /**
     * @ORM\OneToOne(targetEntity="mgate\PersonneBundle\Entity\Employe", mappedBy="personne", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $employe;

    /**
     * @ORM\OneToOne(targetEntity="mgate\UserBundle\Entity\User", mappedBy="personne", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="mgate\PersonneBundle\Entity\Membre", mappedBy="personne", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $membre;

    // pour afficher Prénom Nom
    // Merci de ne pas supprimer
    public function getPrenomNom()
    {
        return $this->prenom.' '.$this->nom;
    }

    public function getNomFormel()
    {
        return $this->sexe.' '.mb_strtoupper($this->nom, 'UTF-8').' '.$this->prenom;
    }

    public function getPoste()
    {
        if ($this->getEmploye()) {
            return $this->getEmploye()->getPoste();
        } elseif ($this->getMembre()) {  //Renvoi le plus haut poste (par id)
            $mandatValid = null;
            if (count($mandats = $this->getMembre()->getMandats())) {
                $id = 100;
                foreach ($mandats as $mandat) {
                    if ($mandat->getPoste()->getId() < $id) {
                        $mandatValid = $mandat;
                    }
                    $id = $mandat->getPoste()->getId();
                }
            }
            if ($mandatValid) {
                return $mandatValid->getPoste()->getIntitule();
            } else {
                return '';
            }
        } else {
            return '';
        }
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
     * Set prenom.
     *
     * @param string $prenom
     *
     * @return Personne
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom.
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set nom.
     *
     * @param string $nom
     *
     * @return Personne
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
     * Set sexe.
     *
     * @param string $sexe
     *
     * @return Personne
     */
    public function setSexe($sexe)
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * Get sexe.
     *
     * @return string
     */
    public function getSexe()
    {
        return $this->sexe;
    }

    /**
     * Set mobile.
     *
     * @param string $mobile
     *
     * @return Personne
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile.
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set fix.
     *
     * @param string $fix
     *
     * @return Personne
     */
    public function setFix($fix)
    {
        $this->fix = $fix;

        return $this;
    }

    /**
     * Get fix.
     *
     * @return string
     */
    public function getFix()
    {
        return $this->fix;
    }

    /**
     * Set adresse.
     *
     * @param string $adresse
     *
     * @return Personne
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

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return Personne
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set employe.
     *
     * @param \mgate\PersonneBundle\Entity\Employe $employe
     *
     * @return Personne
     */
    public function setEmploye(\mgate\PersonneBundle\Entity\Employe $employe = null)
    {
        $this->employe = $employe;

        return $this;
    }

    /**
     * Get employe.
     *
     * @return \mgate\PersonneBundle\Entity\Employe
     */
    public function getEmploye()
    {
        return $this->employe;
    }

    /**
     * Set user.
     *
     * @param \mgate\UserBundle\Entity\User $user
     *
     * @return Personne
     */
    public function setUser(\mgate\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \mgate\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set membre.
     *
     * @param \mgate\PersonneBundle\Entity\Membre $membre
     *
     * @return Personne
     */
    public function setMembre(\mgate\PersonneBundle\Entity\Membre $membre = null)
    {
        $this->membre = $membre;

        return $this;
    }

    /**
     * Get membre.
     *
     * @return \mgate\PersonneBundle\Entity\Membre
     */
    public function getMembre()
    {
        return $this->membre;
    }

    /**
     * Set emailEstValide.
     *
     * @param bool $emailEstValide
     *
     * @return Personne
     */
    public function setEmailEstValide($emailEstValide)
    {
        $this->emailEstValide = $emailEstValide;

        return $this;
    }

    /**
     * Get emailEstValide.
     *
     * @return bool
     */
    public function getEmailEstValide()
    {
        return $this->emailEstValide;
    }

    /**
     * Set estAbonneNewsletter.
     *
     * @param bool $estAbonneNewsletter
     *
     * @return Personne
     */
    public function setEstAbonneNewsletter($estAbonneNewsletter)
    {
        $this->estAbonneNewsletter = $estAbonneNewsletter;

        return $this;
    }

    /**
     * Get estAbonneNewsletter.
     *
     * @return bool
     */
    public function getEstAbonneNewsletter()
    {
        return $this->estAbonneNewsletter;
    }
}
