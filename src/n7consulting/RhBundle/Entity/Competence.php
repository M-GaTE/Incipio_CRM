<?php

namespace n7consulting\RhBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use mgate\PersonneBundle\Entity\Membre;

/**
 * Competence : objet pouvant être attaché à un intervenant ou a une étude pour caractériser ce dont il a besoin.
 * @ORM\Entity(repositoryClass="n7consulting\RhBundle\Entity\CompetenceRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="NameConstraintes", columns={"nom"})})
 */
class Competence
{

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $description;


    /**
     * @var string
     * @ORM\Column(name="nom", type="string", length=15, nullable=false)
     */
    private $nom;

    /**
     * @ORM\ManyToMany(targetEntity="mgate\PersonneBundle\Entity\Membre", inversedBy="competences")
     */
    private $membres;


    /**
     * @var integer
     *
     * @ORM\Column(name="id",type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    public function __construct()
    {
        $this->membres = new ArrayCollection();
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add membres.
     *
     * @param Membre $membres
     *
     * @return Competence
     */
    public function addMembre(Membre $membres)
    {
        $this->membres[] = $membres;

        return $this;
    }

    /**
     * Remove membres.
     *
     * @param Membre $membres
     */
    public function removeMembre(Membre $membres)
    {
        $this->membres->removeElement($membres);
    }

    /**
     * Get membres.
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getMembres()
    {
        return $this->membres;
    }

    public function __toString(){
        return $this->getNom();
    }

}