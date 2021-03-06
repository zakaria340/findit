<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tags
 *
 * @ORM\Table(name="tags")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TagsRepository")
 */
class Tags {

  /**
   * @var int
   *
   * @ORM\Column(name="idTags", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */

  private $idTags;

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
     * Get idTags
     *
     * @return integer
     */
    public function getIdTags()
    {
        return $this->idTags;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Tags
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
     * @return Tags
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
