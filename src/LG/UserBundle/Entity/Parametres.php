<?php

namespace LG\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LG\UserBundle\Entity\Parametres
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Parametres
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var boolean $jour
     *
     * @ORM\Column(name="jour", type="boolean")
     */
    private $jour;

    /**
     * @var string $heureNuit
     *
     * @ORM\Column(name="heureNuit", type="string", length=255)
     */
    private $heureNuit;

    /**
     * @var string $heureJour
     *
     * @ORM\Column(name="heureJour", type="string", length=255)
     */
    private $heureJour;

    /**
     * @var integer $temps
     *
     * @ORM\Column(name="temps", type="integer")
     */
    private $temps;
    
    /**
     * @var boolean $erreur
     *
     * @ORM\Column(name="erreur", type="boolean")
     */
    private $erreur;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set jour
     *
     * @param boolean $jour
     */
    public function setJour($jour)
    {
        $this->jour = $jour;
    }

    /**
     * Get jour
     *
     * @return boolean 
     */
    public function getJour()
    {
        return $this->jour;
    }

    /**
     * Set heureNuit
     *
     * @param string $heureNuit
     */
    public function setHeureNuit($heureNuit)
    {
        $this->heureNuit = $heureNuit;
    }

    /**
     * Get heureNuit
     *
     * @return string 
     */
    public function getHeureNuit()
    {
        return $this->heureNuit;
    }

    /**
     * Set heureJour
     *
     * @param string $heureJour
     */
    public function setHeureJour($heureJour)
    {
        $this->heureJour = $heureJour;
    }

    /**
     * Get heureJour
     *
     * @return string 
     */
    public function getHeureJour()
    {
        return $this->heureJour;
    }

    /**
     * Set temps
     *
     * @param integer $temps
     */
    public function setTemps($temps)
    {
        $this->temps = $temps;
    }

    /**
     * Get temps
     *
     * @return integer 
     */
    public function getTemps()
    {
        return $this->temps;
    }
    
    
    ////////////////////////////     FONCTIONS PERSONALISÉES       \\\\\\\\\\\\\\\\\\
    
    public function jourSuivant() {
        $this->temps += 1;
    }
    

    /**
     * Set erreur
     *
     * @param boolean $erreur
     */
    public function setErreur($erreur)
    {
        $this->erreur = $erreur;
    }

    /**
     * Get erreur
     *
     * @return boolean 
     */
    public function getErreur()
    {
        return $this->erreur;
    }
}