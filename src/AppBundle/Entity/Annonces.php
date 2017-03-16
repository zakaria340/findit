<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Annonces
 *
 * @ORM\Table(name="annonces")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AnnoncesRepository")
 */
class Annonces {

  /**
   * @var int
   *
   * @ORM\Column(name="idAnnonces", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */

  private $idAnnonces;

  /**
   * @var string
   *
   * @ORM\Column(name="idSite", type="string", length=255)
   */

  private $idSite;

  /**
   * @var string
   *
   * @ORM\Column(name="title", type="string", length=255)
   */

  private $title;

  /**
   * @var string
   *
   * @ORM\Column(name="description", type="text")
   */

  private $description;

  /**
   * @var string
   *
   * @ORM\Column(name="date", type="integer", length=11)
   */

  private $date;

  /**
   * @var string
   *
   * @ORM\Column(name="ville", type="string", length=255)
   */

  private $ville;

  /**
   * @var string
   *
   * @ORM\Column(name="tags", type="string", length=255)
   */

  private $tags;

  /**
   * @var string
   *
   * @ORM\Column(name="image", type="string", length=255)
   */

  private $image;

  /**
   * @var string
   *
   * @ORM\Column(name="prix", type="string", length=255)
   */

  private $prix;

  /**
   * @var string
   *
   * @ORM\Column(name="url", type="string", length=255)
   */

  private $url;

  /**
   * @var string
   *
   * @ORM\Column(name="extraKeywords", type="string", length=255)
   */

  private $extraKeywords;

  /**
   * Get idAnnonces
   *
   * @return integer
   */
  public function getIdAnnonces() {
    return $this->idAnnonces;
  }

  /**
   * Set idSite
   *
   * @param string $idSite
   *
   * @return Annonces
   */
  public function setIdSite($idSite) {
    $this->idSite = $idSite;

    return $this;
  }

  /**
   * Get idSite
   *
   * @return string
   */
  public function getIdSite() {
    return $this->idSite;
  }

  /**
   * Set title
   *
   * @param string $title
   *
   * @return Annonces
   */
  public function setTitle($title) {
    $this->title = $title;

    return $this;
  }

  /**
   * Get title
   *
   * @return string
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * Set description
   *
   * @param string $description
   *
   * @return Annonces
   */
  public function setDescription($description) {
    $this->description = $description;

    return $this;
  }

  /**
   * Get description
   *
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * Set date
   *
   * @param \int $date
   *
   * @return Annonces
   */
  public function setDate($date) {
    $this->date = $date;

    return $this;
  }

  /**
   * Get date
   *
   * @return \int
   */
  public function getDate() {
    return $this->date;
  }

  /**
   * Set ville
   *
   * @param string $ville
   *
   * @return Annonces
   */
  public function setVille($ville) {
    $this->ville = $ville;

    return $this;
  }

  /**
   * Get ville
   *
   * @return string
   */
  public function getVille() {
    return $this->ville;
  }

  /**
   * Set tags
   *
   * @param string $tags
   *
   * @return Annonces
   */
  public function setTags($tags) {
    $this->tags = $tags;

    return $this;
  }

  /**
   * Get tags
   *
   * @return string
   */
  public function getTags() {
    return $this->tags;
  }

  /**
   * Set image
   *
   * @param string $image
   *
   * @return Annonces
   */
  public function setImage($image) {
    $this->image = $image;

    return $this;
  }

  /**
   * Get image
   *
   * @return string
   */
  public function getImage() {
    return $this->image;
  }

  /**
   * Set prix
   *
   * @param string $prix
   *
   * @return Annonces
   */
  public function setPrix($prix) {
    $this->prix = $prix;

    return $this;
  }

  /**
   * Get prix
   *
   * @return string
   */
  public function getPrix() {
    return $this->prix;
  }

  /**
   * Set url
   *
   * @param string $url
   *
   * @return Annonces
   */
  public function setUrl($url) {
    $this->url = $url;

    return $this;
  }

  /**
   * Get url
   *
   * @return string
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * Set extraKeywords
   *
   * @param string $extraKeywords
   *
   * @return Annonces
   */
  public function setExtraKeywords($extraKeywords) {
    $this->extraKeywords = $extraKeywords;

    return $this;
  }

  /**
   * Get extraKeywords
   *
   * @return string
   */
  public function getExtraKeywords() {
    return $this->extraKeywords;
  }

  /*
   * 
   */
  public function getExtraAnnonces() {
    return json_decode($this->extraKeywords);
  }
}
