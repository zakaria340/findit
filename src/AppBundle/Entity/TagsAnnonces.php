<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tags
 *
 * @ORM\Table(name="tagsAnnonces")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TagsAnnoncesRepository")
 */
class TagsAnnonces {

  /**
   * @var int
   *
   * @ORM\Column(name="idTagAnnonces", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */

  private $idTagAnnonces;

  /**
   * @var string
   *
   * @ORM\Column(name="idAnnonce", type="string", length=255)
   */

  private $idAnnonce;


  /**
   * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TagsExtra")
   * @ORM\JoinColumn(name="idTags", referencedColumnName="idTagsExtra")
   */
  private $idTags;

  /**
   * @var string
   *
   * @ORM\Column(name="value", type="string", length=255)
   */

  private $Value;


    /**
     * Get idTagAnnonces
     *
     * @return integer
     */
    public function getIdTagAnnonces()
    {
        return $this->idTagAnnonces;
    }

    /**
     * Set idAnnonce
     *
     * @param string $idAnnonce
     *
     * @return TagsAnnonces
     */
    public function setIdAnnonce($idAnnonce)
    {
        $this->idAnnonce = $idAnnonce;

        return $this;
    }

    /**
     * Get idAnnonce
     *
     * @return string
     */
    public function getIdAnnonce()
    {
        return $this->idAnnonce;
    }

    /**
     * Set idTags
     *
     * @param string $idTags
     *
     * @return TagsAnnonces
     */
    public function setIdTags($idTags)
    {
        $this->idTags = $idTags;

        return $this;
    }

    /**
     * Get idTags
     *
     * @return string
     */
    public function getIdTags()
    {
        return $this->idTags;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return TagsAnnonces
     */
    public function setValue($value)
    {
        $this->Value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->Value;
    }
}
