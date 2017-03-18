<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\SuiviBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mgate\SuiviBundle\Entity\Cc.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Cc extends DocType
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
     * @ORM\OneToOne(targetEntity="Etude", mappedBy="cc")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    protected $etude;

   /*
    * ADDITIONAL
    */
    public function getReference()
    {
        return $this->etude->getReference().'/'.$this->getDateSignature()->format('Y').'/CC/'.$this->getVersion();
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
     * @param Mgate\SuiviBundle\Entity\Etude $etude
     *
     * @return Cc
     */
    public function setEtude(Etude $etude = null)
    {
        $this->etude = $etude;

        return $this;
    }

    /**
     * Get etude.
     *
     * @return Mgate\SuiviBundle\Entity\Etude
     */
    public function getEtude()
    {
        return $this->etude;
    }

    public function __toString()
    {
        return $this->etude->getReference().'/CC/';
    }
}
