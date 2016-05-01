<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\User
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Role")
     */
    private $role;

    /**
     * @var boolean $vivant
     *
     * @ORM\Column(name="vivant", type="boolean", nullable=true)
     * 
     */
    private $vivant;
    
    /**
     * @var integer $promo
     *
     * @ORM\Column(name="promo", type="integer", nullable=true)
     */
    private $promo;
    
    /**
     * @var string $nom
     *
     * @ORM\Column(name="nom", type="string", nullable=true)
     */
    private $nom;
    
    /**
     * @var string $prenom
     *
     * @ORM\Column(name="prenom", type="string", nullable=true)
     */
    private $prenom;
    
    /**
     * @var integer $idextra
     *
     * @ORM\Column(name="idextra", type="integer", nullable=true)
     */
    private $idextra;
    
    /**
     * @var boolean $maire
     *
     * @ORM\Column(name="maire", type="boolean", nullable=true)
     */
    private $maire;
    
    /**
     * @var date $dateMort
     *
     * @ORM\Column(name="dateMort", type="date", nullable=true)
     */
    private $dateMort;

    //1= mort des vilageois, 2= mort dévoré
    /**
     * @var int $typeMort
     *
     * @ORM\Column(name="typeMort", type="integer", nullable=true)
     */
    private $typeMort;
    
    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
    
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
     * Set vivant
     *
     * @param boolean $vivant
     */
    public function setVivant($vivant)
    {
        $this->vivant = $vivant;
    }

    /**
     * Get vivant
     *
     * @return boolean 
     */
    public function getVivant()
    {
        return $this->vivant;
    }

    /**
     * Set role
     *
     * @param AppBundle\Entity\Role $role
     */
    public function setRole(\AppBundle\Entity\Role $role)
    {
        $this->role = $role;
    }

    /**
     * Get role
     *
     * @return AppBundle\Entity\Role 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set promo
     *
     * @param integer $promo
     */
    public function setPromo($promo)
    {
        $this->promo = $promo;
    }

    /**
     * Get promo
     *
     * @return integer 
     */
    public function getPromo()
    {
        return $this->promo;
    }

    /**
     * Set nom
     *
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /**
     * Get prenom
     *
     * @return string 
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set idextra
     *
     * @param integer $idextra
     */
    public function setIdextra($idextra)
    {
        $this->idextra = $idextra;
    }

    /**
     * Get idextra
     *
     * @return integer 
     */
    public function getIdextra()
    {
        return $this->idextra;
    }

    /**
     * Set maire
     *
     * @param boolean $maire
     */
    public function setMaire($maire)
    {
        $this->maire = $maire;
    }

    /**
     * Get maire
     *
     * @return boolean 
     */
    public function getMaire()
    {
        return $this->maire;
    }

    /**
     * Set dateMort
     *
     * @param date $dateMort
     */
    public function setDateMort($dateMort)
    {
        $this->dateMort = $dateMort;
    }

    /**
     * Get dateMort
     *
     * @return date 
     */
    public function getDateMort()
    {
        return $this->dateMort;
    }

    /**
     * Set typeMort
     *
     * @param integer $typeMort
     */
    public function setTypeMort($typeMort)
    {
        $this->typeMort = $typeMort;
    }

    /**
     * Get typeMort
     *
     * @return integer 
     */
    public function getTypeMort()
    {
        return $this->typeMort;
    }
}
