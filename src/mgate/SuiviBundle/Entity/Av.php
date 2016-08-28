<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace mgate\SuiviBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use mgate\SuiviBundle\Entity\AvMission;
use mgate\SuiviBundle\Entity\Etude;
use mgate\SuiviBundle\Entity\Phase;

/**
 * mgate\SuiviBundle\Entity\Av.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Av extends DocType
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
     * @ORM\ManyToOne(targetEntity="Etude", inversedBy="avs", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $etude;

    /**
     * @ORM\Column(name="differentielDelai", type="integer", nullable=false,  options={"default"=0})
     *
     * @var date
     */
    private $differentielDelai;

    /**
     * @ORM\Column(name="objet", type="text", nullable=false)
     *
     * @var string
     */
    private $objet;

    /**
     * @var AvMission
     * @ORM\OneToMany(targetEntity="mgate\SuiviBundle\Entity\AvMission", mappedBy="avenant", cascade={"persist","remove"})
     */
    private $avenantsMissions;

    /**
     * @var array
     * @ORM\Column(name="clauses", type="array")
     */
    private $clauses;

    /**
     * @var Collection phase differentiel
     * @ORM\OneToMany(targetEntity="mgate\SuiviBundle\Entity\Phase", mappedBy="avenant", cascade={"persist", "remove"})
     */
    private $phases;

    public static function getClausesChoices()
    {
        return array(1 => 'Avenant de Délai',
            2 => 'Avenant de Méthodologie',
            3 => 'Avenant de Montant',
            4 => 'Avenant de Mission',
            5 => 'Avenant de Rupture', );
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
     * Set etude.
     *
     * @param mgate\SuiviBundle\Entity\Etude $etude
     *
     * @return Av
     */
    public function setEtude(Etude $etude)
    {
        $this->etude = $etude;

        return $this;
    }

    /**
     * Get etude.
     *
     * @return mgate\SuiviBundle\Entity\Etude
     */
    public function getEtude()
    {
        return $this->etude;
    }
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->avenantsMissions = new ArrayCollection();
        $this->phases = new ArrayCollection();
    }

    /**
     * Set differentielDelai.
     *
     * @param int $differentielDelai
     *
     * @return Av
     */
    public function setDifferentielDelai($differentielDelai)
    {
        $this->differentielDelai = $differentielDelai;

        return $this;
    }

    /**
     * Get differentielDelai.
     *
     * @return int
     */
    public function getDifferentielDelai()
    {
        return $this->differentielDelai;
    }

    /**
     * Set objet.
     *
     * @param string $objet
     *
     * @return Av
     */
    public function setObjet($objet)
    {
        $this->objet = $objet;

        return $this;
    }

    /**
     * Get objet.
     *
     * @return string
     */
    public function getObjet()
    {
        return $this->objet;
    }

    /**
     * Add avenantsMissions.
     *
     * @param AvMission $avenantsMissions
     *
     * @return Av
     */
    public function addAvenantsMission(AvMission $avenantsMissions)
    {
        $this->avenantsMissions[] = $avenantsMissions;

        return $this;
    }

    /**
     * Remove avenantsMissions.
     *
     * @param AvMission $avenantsMissions
     */
    public function removeAvenantsMission(AvMission $avenantsMissions)
    {
        $this->avenantsMissions->removeElement($avenantsMissions);
    }

    /**
     * Get avenantsMissions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAvenantsMissions()
    {
        return $this->avenantsMissions;
    }

    /**
     * Set clauses.
     *
     * @param array $clauses
     *
     * @return Av
     */
    public function setClauses($clauses)
    {
        $this->clauses = $clauses;

        return $this;
    }

    /**
     * Get clauses.
     *
     * @return array
     */
    public function getClauses()
    {
        return $this->clauses;
    }

    /**
     * Add phases.
     *
     * @param Phase $phases
     *
     * @return Av
     */
    public function addPhase(Phase $phases)
    {
        $this->phases[] = $phases;

        return $this;
    }

    /**
     * Remove phases.
     *
     * @param Phase $phases
     */
    public function removePhase(Phase $phases)
    {
        $this->phases->removeElement($phases);
    }

    /**
     * Get phases.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPhases()
    {
        return $this->phases;
    }

    public function __toString()
    {
        return $this->etude->getReference().'/AV/'.$this->getId();
    }
}
