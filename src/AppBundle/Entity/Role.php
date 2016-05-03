<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\Role
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Role
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
     * @var string $nom
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var string $image
     *
     * @ORM\Column(name="image", type="string", length=255)
     */
    private $image;

    /**
     * @var boolean $pouvoir1
     *
     * @ORM\Column(name="pouvoir1", type="boolean")
     */
    private $pouvoir1;
    
    /** @var string $pouvoir1desc
     *
     * @ORM\Column(name="pouvoir1desc", type="string", length=255, options={"default":""})
     */
    private $pouvoir1desc='';

    /**
     * @var boolean $pouvoir2
     *
     * @ORM\Column(name="pouvoir2", type="boolean")
     */
    private $pouvoir2;

    /** @var string $pouvoir2desc
     *
     * @ORM\Column(name="pouvoir2desc", type="string", length=255, options={"default":""})
     */
    private $pouvoir2desc='';

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
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set image
     *
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set pouvoir1
     *
     * @param boolean $pouvoir1
     */
    public function setPouvoir1($pouvoir1)
    {
        $this->pouvoir1 = $pouvoir1;
    }

    /**
     * Get pouvoir1
     *
     * @return boolean 
     */
    public function getPouvoir1()
    {
        return $this->pouvoir1;
    }

    /**
     * Set pouvoir2
     *
     * @param boolean $pouvoir2
     */
    public function setPouvoir2($pouvoir2)
    {
        $this->pouvoir2 = $pouvoir2;
    }

    /**
     * Get pouvoir2
     *
     * @return boolean 
     */
    public function getPouvoir2()
    {
        return $this->pouvoir2;
    }

    /**
     * Set pouvoir1desc
     *
     * @param string $pouvoir1desc
     */
    public function setPouvoir1desc($pouvoir1desc)
    {
        $this->pouvoir1desc = $pouvoir1desc;
    }

    /**
     * Get pouvoir1desc
     *
     * @return string 
     */
    public function getPouvoir1desc()
    {
        return $this->pouvoir1desc;
    }

    /**
     * Set pouvoir2desc
     *
     * @param string $pouvoir2desc
     */
    public function setPouvoir2desc($pouvoir2desc)
    {
        $this->pouvoir2desc = $pouvoir2desc;
    }

    /**
     * Get pouvoir2desc
     *
     * @return string 
     */
    public function getPouvoir2desc()
    {
        return $this->pouvoir2desc;
    }
}
