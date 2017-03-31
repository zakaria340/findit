<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tags
 *
 * @ORM\Table(name="tagsExtra")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TagsExtraRepository")
 */
class TagsExtra {

  /**
   * @var int
   *
   * @ORM\Column(name="idTagsExtra", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */

  private $idTagsExtra;

  /**
   * @var string
   *
   * @ORM\Column(name="slug", type="string", length=255)
   */

  private $slug;

  /**
   * @var string
   *
   * @ORM\Column(name="title", type="string", length=255)
   */

  private $Title;


    /**
     * Get idTagsExtra
     *
     * @return integer
     */
    public function getIdTagsExtra()
    {
        return $this->idTagsExtra;
    }

    /**
     * Set idAnnonce
     *
     * @param integer $idAnnonce
     *
     * @return TagsExtra
     */
    public function setIdAnnonce($idAnnonce)
    {
        $this->idAnnonce = $idAnnonce;

        return $this;
    }

    /**
     * Get idAnnonce
     *
     * @return integer
     */
    public function getIdAnnonce()
    {
        return $this->idAnnonce;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return TagsExtra
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

    /**
     * Set title
     *
     * @param string $title
     *
     * @return TagsExtra
     */
    public function setTitle($title)
    {
        $this->Title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->Title;
    }
}
