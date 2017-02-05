<?php
/**
 * Created by PhpStorm.
 * User: Antoine
 * Date: 13/11/2016
 * Time: 15:53.
 */
namespace Mgate\PersonneBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\MappedSuperclass
 * Class gathering adresse related informations. Factorize code in an unique class.
 */
class Adressable
{
    /**
     * @var string
     *
     * @ORM\Column(name="adresse", type="string", length=127, nullable=true)
     */
    private $adresse;

    /**
     * @var int(5)
     *
     * @ORM\Column(name="codepostal", type="integer", nullable=true)
     */
    private $codepostal;

    /**
     * @var string
     *
     * @ORM\Column(name="ville", type="string", length=63, nullable=true)
     */
    private $ville;

    /**
     * @var string
     *
     * @ORM\Column(name="pays", type="string", length=63, nullable=true)
     */
    private $pays;

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
     * Set codepostal.
     *
     * @param int $codepostal
     *
     * @return Prospect
     */
    public function setCodePostal($codepostal)
    {
        $this->codepostal = $codepostal;

        return $this;
    }

    /**
     * Get codePostal.
     *
     * @return int
     */
    public function getCodePostal()
    {
        return $this->codepostal;
    }

    /**
     * Set ville.
     *
     * @param string $ville
     *
     * @return Prospect
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville.
     *
     * @return string
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set codepostal.
     *
     * @param string $pays
     *
     * @return Prospect
     */
    public function setPays($pays)
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * Get pays.
     *
     * @return string
     */
    public function getPays()
    {
        return $this->pays;
    }
}
