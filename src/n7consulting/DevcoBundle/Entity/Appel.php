<?php

namespace n7consulting\DevcoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use mgate\PersonneBundle\Entity\Prospect;
use mgate\PersonneBundle\Entity\Employe;
use mgate\PersonneBundle\Entity\Membre;

/**
 * Appel : objet représentant un appel de prospection passé ou à passer.
 *
 * @ORM\Entity(repositoryClass="n7consulting\DevcoBundle\Entity\AppelRepository")
 */
class Appel
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $noteAppel;

    /**
     * @ORM\ManyToOne(targetEntity="mgate\PersonneBundle\Entity\Membre", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $suiveur;

    /**
     * @ORM\ManyToOne(targetEntity="mgate\PersonneBundle\Entity\Employe", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $employe;

    /**
     * @ORM\ManyToOne(targetEntity="mgate\PersonneBundle\Entity\Prospect", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $prospect;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateAppel", type="date")
     */
    private $dateAppel;

    /**
     * @var bool
     * @ORM\Column(name="aRappeller", type="boolean", nullable=false, options={"default" = true})
     */
    private $aRappeller;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateRappel", type="date", nullable = true)
     */
    private $dateRappel;

    /**
     * @var int
     *
     * @ORM\Column(name="id",type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @return string
     */
    public function getNoteAppel()
    {
        return $this->noteAppel;
    }

    /**
     * @param string $noteAppel
     */
    public function setNoteAppel($noteAppel)
    {
        $this->noteAppel = $noteAppel;
    }

    /**
     * @return mixed
     */
    public function getSuiveur()
    {
        return $this->suiveur;
    }

    /**
     * @param mixed $suiveur
     */
    public function setSuiveur(Membre $suiveur)
    {
        $this->suiveur = $suiveur;
    }

    /**
     * @return Employe
     */
    public function getEmploye()
    {
        return $this->employe;
    }

    /**
     * @param Employe $employe
     */
    public function setEmploye(Employe $employe)
    {
        $this->employe = $employe;
    }

    /**
     * @return Prospect
     */
    public function getProspect()
    {
        return $this->prospect;
    }

    /**
     * @param Prospect $prospect
     */
    public function setProspect(Prospect $prospect)
    {
        $this->prospect = $prospect;
    }

    /**
     * @return \DateTime
     */
    public function getDateAppel()
    {
        return $this->dateAppel;
    }

    /**
     * @param \DateTime $dateAppel
     */
    public function setDateAppel($dateAppel)
    {
        $this->dateAppel = $dateAppel;
    }

    /**
     * @return bool
     */
    public function isARappeller()
    {
        return $this->aRappeller;
    }

    /**
     * @param bool $aRappeller
     */
    public function setARappeller($aRappeller)
    {
        $this->aRappeller = $aRappeller;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDateRappel()
    {
        return $this->dateRappel;
    }

    /**
     * @param \DateTime $dateRappel
     */
    public function setDateRappel($dateRappel)
    {
        $this->dateRappel = $dateRappel;
    }
}
