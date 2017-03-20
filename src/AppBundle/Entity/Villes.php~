<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tags
 *
 * @ORM\Table(name="villes")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VillesRepository")
 */
class Villes {

  /**
   * @var int
   *
   * @ORM\Column(name="idVilles", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */

  private $idVilles;

  /**
   * @var string
   *
   * @ORM\Column(name="name", type="string", length=255)
   */

  private $name;


  /**
   * @var string
   *
   * @ORM\Column(name="slug", type="string", length=255)
   */

  private $slug;


    /**
     * Get idVilles
     *
     * @return integer
     */
    public function getIdVilles()
    {
        return $this->idVilles;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Villes
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Villes
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
}
